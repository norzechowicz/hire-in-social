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

namespace ITOffers\Tests\Offers\Application\MotherObject\Command\Offer;

use Faker\Factory;
use ITOffers\Offers\Application\Command\Offer\Offer\Company;
use ITOffers\Offers\Application\Command\Offer\Offer\Contact;
use ITOffers\Offers\Application\Command\Offer\Offer\Contract;
use ITOffers\Offers\Application\Command\Offer\Offer\Description;
use ITOffers\Offers\Application\Command\Offer\Offer\Description\Requirements;
use ITOffers\Offers\Application\Command\Offer\Offer\Description\Requirements\Skill;
use ITOffers\Offers\Application\Command\Offer\Offer\Location;
use ITOffers\Offers\Application\Command\Offer\Offer\Location\LatLng;
use ITOffers\Offers\Application\Command\Offer\Offer\Offer;
use ITOffers\Offers\Application\Command\Offer\Offer\Position;
use ITOffers\Offers\Application\Command\Offer\Offer\Salary;
use ITOffers\Offers\Application\Command\Offer\PostOffer;
use ITOffers\Offers\Application\Offer\Position\SeniorityLevels;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Salary as SalaryView;

final class PostOfferMother
{
    public static function random(string $offerId, string $userId, string $specialization) : PostOffer
    {
        $faker = Factory::create();

        return new PostOffer(
            $offerId,
            $specialization,
            'en_US',
            $userId,
            new Offer(
                new Company($faker->company, $faker->url, $faker->text(512)),
                new Position(\random_int(SeniorityLevels::INTERN, SeniorityLevels::EXPERT), 'PHP Developer'),
                new Location($faker->boolean, $faker->countryCode, $faker->city, $faker->address, new LatLng($faker->latitude, $faker->longitude)),
                new Salary($faker->numberBetween(1_000, 5_000), $faker->numberBetween(5_000, 20_000), 'PLN', $faker->boolean, SalaryView::PERIOD_TYPE_MONTH),
                new Contract('B2B'),
                new Description(
                    $faker->text(1_024),
                    $faker->text(1_024),
                    new Requirements(
                        $faker->text(1_024),
                        new Skill('php', true, 5)
                    )
                ),
                Contact::recruiter(
                    $faker->email,
                    $faker->name,
                    '+1 333333333'
                ),
            )
        );
    }

    public static function randomWithPDF(string $offerId, string $userId, string $specialization, string $offerPDFPath) : PostOffer
    {
        $faker = Factory::create();

        return new PostOffer(
            $offerId,
            $specialization,
            'en_US',
            $userId,
            new Offer(
                new Company($faker->company, $faker->url, $faker->text(512)),
                new Position(\random_int(SeniorityLevels::INTERN, SeniorityLevels::EXPERT), 'PHP Developer'),
                new Location($faker->boolean, $faker->countryCode, $faker->city, $faker->address, new LatLng($faker->latitude, $faker->longitude)),
                new Salary($faker->numberBetween(1_000, 5_000), $faker->numberBetween(5_000, 20_000), 'PLN', $faker->boolean, SalaryView::PERIOD_TYPE_MONTH),
                new Contract('B2B'),
                new Description(
                    $faker->text(1_024),
                    $faker->text(1_024),
                    new Requirements(
                        $faker->text(1_024),
                        new Skill('php', true, 5)
                    )
                ),
                Contact::recruiter(
                    $faker->email,
                    $faker->name,
                    '+1 333333333'
                ),
            ),
            $offerPDFPath
        );
    }

    public static function withoutSalary(string $offerId, string $userId, string $specialization) : PostOffer
    {
        $faker = Factory::create();

        return new PostOffer(
            $offerId,
            $specialization,
            'en_US',
            $userId,
            new Offer(
                new Company($faker->company, $faker->url, $faker->text(512)),
                new Position(\random_int(SeniorityLevels::INTERN, SeniorityLevels::EXPERT), 'PHP Developer'),
                new Location($faker->boolean, $faker->countryCode, $faker->city, $faker->address, new LatLng($faker->latitude, $faker->longitude)),
                null,
                new Contract('B2B'),
                new Description(
                    $faker->text(1_024),
                    $faker->text(1_024),
                    new Requirements(
                        $faker->text(1_024),
                        new Skill('php', true, 5)
                    )
                ),
                Contact::recruiter(
                    $faker->email,
                    $faker->name,
                    '+1 333333333'
                ),
            )
        );
    }

    public static function withSalary(string $offerId, string $userId, string $specialization, int $min, int $max, string $currency = 'USD') : PostOffer
    {
        $faker = Factory::create();

        return new PostOffer(
            $offerId,
            $specialization,
            'en_US',
            $userId,
            new Offer(
                new Company($faker->company, $faker->url, $faker->text(512)),
                new Position(\random_int(SeniorityLevels::INTERN, SeniorityLevels::EXPERT), 'PHP Developer'),
                new Location($faker->boolean, $faker->countryCode, $faker->city, $faker->address, new LatLng($faker->latitude, $faker->longitude)),
                new Salary($min, $max, $currency, $faker->boolean, SalaryView::PERIOD_TYPE_MONTH),
                new Contract('B2B'),
                new Description(
                    $faker->text(1_024),
                    $faker->text(1_024),
                    new Requirements(
                        $faker->text(1_024),
                        new Skill('php', true, 5)
                    )
                ),
                Contact::recruiter(
                    $faker->email,
                    $faker->name,
                    '+1 333333333'
                ),
            )
        );
    }

    public static function notRemote(string $offerId, string $userId, string $specialization) : PostOffer
    {
        $faker = Factory::create();

        return new PostOffer(
            $offerId,
            $specialization,
            'en_US',
            $userId,
            new Offer(
                new Company($faker->company, $faker->url, $faker->text(512)),
                new Position(\random_int(SeniorityLevels::INTERN, SeniorityLevels::EXPERT), 'PHP Developer'),
                new Location(false, $faker->countryCode, $faker->city, $faker->address, new LatLng($faker->latitude, $faker->longitude)),
                new Salary($faker->numberBetween(1_000, 5_000), $faker->numberBetween(5_000, 20_000), 'PLN', $faker->boolean, SalaryView::PERIOD_TYPE_MONTH),
                new Contract('B2B'),
                new Description(
                    $faker->text(1_024),
                    $faker->text(1_024),
                    new Requirements(
                        $faker->text(1_024),
                        new Skill('php', true, 5)
                    )
                ),
                Contact::recruiter(
                    $faker->email,
                    $faker->name,
                    '+1 333333333'
                ),
            )
        );
    }

    public static function remote(string $offerId, string $userId, string $specialization) : PostOffer
    {
        $faker = Factory::create();

        return new PostOffer(
            $offerId,
            $specialization,
            'en_US',
            $userId,
            new Offer(
                new Company($faker->company, $faker->url, $faker->text(512)),
                new Position(\random_int(SeniorityLevels::INTERN, SeniorityLevels::EXPERT), 'PHP Developer'),
                new Location(true),
                new Salary($faker->numberBetween(1_000, 5_000), $faker->numberBetween(5_000, 20_000), 'PLN', $faker->boolean, SalaryView::PERIOD_TYPE_MONTH),
                new Contract('B2B'),
                new Description(
                    $faker->text(1_024),
                    $faker->text(1_024),
                    new Requirements(
                        $faker->text(1_024),
                        new Skill('php', true, 5)
                    )
                ),
                Contact::recruiter(
                    $faker->email,
                    $faker->name,
                    '+1 333333333'
                ),
            )
        );
    }

    public static function onFB(string $offerId, string $fbUserId, string $specialization) : PostOffer
    {
        $faker = Factory::create();

        return new PostOffer(
            $offerId,
            $specialization,
            'en_US',
            $fbUserId,
            new Offer(
                new Company($faker->company, $faker->url, $faker->text(512)),
                new Position(\random_int(SeniorityLevels::INTERN, SeniorityLevels::EXPERT), 'PHP Developer'),
                new Location($faker->boolean, $faker->countryCode, $faker->city, $faker->address, new LatLng($faker->latitude, $faker->longitude)),
                new Salary($faker->numberBetween(1_000, 5_000), $faker->numberBetween(5_000, 20_000), 'PLN', $faker->boolean, SalaryView::PERIOD_TYPE_MONTH),
                new Contract('B2B'),
                new Description(
                    $faker->text(1_024),
                    $faker->text(1_024),
                    new Requirements(
                        $faker->text(1_024),
                        new Skill('php', true, 5)
                    )
                ),
                Contact::recruiter(
                    $faker->email,
                    $faker->name,
                    '+1 333333333'
                ),
            )
        );
    }
}
