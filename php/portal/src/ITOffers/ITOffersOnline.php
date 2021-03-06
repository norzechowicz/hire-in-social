<?php

declare(strict_types=1);

/*
 * This file is part of the itoffers.online project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ITOffers;

use Aeon\Calendar\Doctrine\Gregorian\DateTimeType;
use Aeon\Calendar\Gregorian\Calendar;
use Aeon\Calendar\Gregorian\GregorianCalendar;
use Aeon\Calendar\Gregorian\GregorianCalendarStub;
use Aeon\Calendar\Gregorian\TimeZone;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\PredisCache;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Proxy\ProxyFactory;
use ITOffers\Component\EventBus\Infrastructure\InMemory\InMemoryEventBus;
use ITOffers\Component\Mailer\Infrastructure\SwiftMailer\SwiftMailer;
use ITOffers\Component\Mailer\Mailer;
use function ITOffers\Notifications\Infrastructure\notificationsFacade;
use ITOffers\Notifications\Notifications;
use ITOffers\Offers\Infrastructure\Doctrine\DBAL\Platform\PostgreSQL11Platform;
use ITOffers\Offers\Infrastructure\Doctrine\DBAL\Types\Offer\Description\Requirements\SkillsType;
use ITOffers\Offers\Infrastructure\Doctrine\DBAL\Types\Offer\SalaryType;
use function ITOffers\Offers\Infrastructure\offersFacade;
use ITOffers\Offers\Offers;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Twig\Environment;

final class ITOffersOnline
{
    private bool $booted;

    private Config $config;

    private LoggerInterface $logger;

    private Environment $templatingEngine;

    private Swift_Mailer $mailer;

    private ?Offers $offers = null;

    private ?Notifications $notifications = null;

    private ?Connection $connection = null;

    private ?EntityManager $orm = null;

    private ?Calendar $calendar = null;

    private ?InMemoryEventBus $eventBus = null;

    public function __construct(Config $config, LoggerInterface $logger, Environment $twig, Swift_Mailer $mailer)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->templatingEngine = $twig;
        $this->mailer = $mailer;
        $this->booted = false;
    }

    public function boot() : void
    {
        if ($this->booted === false) {
            $this->offers();
            $this->notifications();
        }
    }

    /**
     * Offers Module
     */
    public function offers() : Offers
    {
        if ($this->offers === null) {
            $this->offers = offersFacade($this->config(), $this->orm(), $this->mailer(), $this->templatingEngine(), $this->calendar(), $this->eventBus(), $this->logger());
        }

        return $this->offers;
    }

    /**
     * Notifications Module
     */
    public function notifications() : Notifications
    {
        if (null === $this->notifications) {
            $this->notifications = notificationsFacade(
                $this->config(),
                $this->eventBus(),
                $this->offers(),
                $this->mailer(),
                $this->templatingEngine(),
                $this->logger(),
            );

            if ($this->config()->getBool(Config::NOTIFICATIONS_DISABLED)) {
                $this->notifications->disable();
            }
        }

        return $this->notifications;
    }

    public function config() : Config
    {
        return $this->config;
    }

    public function isDevMode() : bool
    {
        return $this->config()->getString(Config::ENV) !== 'prod';
    }

    public function isTestEnvironment() : bool
    {
        return $this->config()->getString(Config::ENV) === 'test';
    }

    public function isProdEnvironment() : bool
    {
        return $this->config()->getString(Config::ENV) === 'prod';
    }

    public function logger() : LoggerInterface
    {
        return $this->logger;
    }

    public function dbal() : Connection
    {
        if (null !== $this->connection) {
            return $this->connection;
        }

        if (!Type::hasType(DateTimeType::NAME)) {
            Type::addType(DateTimeType::NAME, DateTimeType::class);
        }

        if (!Type::hasType(SalaryType::NAME)) {
            Type::addType(SalaryType::NAME, SalaryType::class);
        }

        if (!Type::hasType(SkillsType::NAME)) {
            Type::addType(SkillsType::NAME, SkillsType::class);
        }

        $this->connection = DriverManager::getConnection(
            [
                'dbname' => $this->config()->getString(Config::DB_NAME),
                'user' => $this->config()->getString(Config::DB_USER),
                'password' => $this->config()->getString(Config::DB_USER_PASS),
                'host' => $this->config()->getString(Config::DB_HOST),
                'port' => $this->config()->getInt(Config::DB_PORT),
                'driver' => 'pdo_pgsql',
                'platform' => new PostgreSQL11Platform(),
            ],
            new Configuration()
        );

        return $this->connection;
    }

    public function orm() : EntityManager
    {
        if (null !== $this->orm) {
            return $this->orm;
        }
        $configuration = new \Doctrine\ORM\Configuration();

        $configuration->setMetadataDriverImpl(new SimplifiedXmlDriver(
            [
                $this->config()->getString(Config::ROOT_PATH) . '/db/orm/mapping/xml' => 'ITOffers\Offers\Application',
            ]
        ));

        $configuration->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

        if ($this->isDevMode()) {
            $cache = new ArrayCache;
        } else {
            $cache = new PredisCache(new Client($this->config()->getString(Config::REDIS_DSN) . '/' . $this->config()->getInt(Config::REDIS_DB_DOCTRINE_CACHE)));
        }

        $configuration->setMetadataCacheImpl($cache);
        $configuration->setQueryCacheImpl($cache);

        $configuration->setProxyDir($this->config()->getString(Config::CACHE_PATH) . '/doctrine/orm');
        $configuration->setProxyNamespace('DoctrineProxy');
        $configuration->setAutoGenerateProxyClasses($this->isDevMode());

        if ($this->isDevMode()) {
            $configuration->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_EVAL);
        }

        $this->orm = EntityManager::create($this->dbal(), $configuration);

        return $this->orm;
    }

    public function calendar() : Calendar
    {
        if ($this->calendar !== null) {
            return $this->calendar;
        }

        if ($this->isTestEnvironment()) {
            $this->calendar = new GregorianCalendarStub();
        } else {
            $this->calendar = new GregorianCalendar(new TimeZone($this->config->getString(Config::TIMEZONE)));
        }

        return $this->calendar;
    }

    public function mailer() : Mailer
    {
        return new SwiftMailer($this->config()->getString(Config::DOMAIN), $this->mailer);
    }

    public function eventBus() : InMemoryEventBus
    {
        if (null !== $this->eventBus) {
            return $this->eventBus;
        }

        $this->eventBus = new InMemoryEventBus();

        return $this->eventBus;
    }

    private function templatingEngine() : Environment
    {
        return $this->templatingEngine;
    }
}
