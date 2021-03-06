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

namespace ITOffers\Offers\Infrastructure\Doctrine\DBAL\Application\Specialization;

use Aeon\Calendar\Gregorian\DateTime;
use Doctrine\DBAL\Connection;
use ITOffers\Offers\Application\Query\Specialization\Model\Specialization;
use ITOffers\Offers\Application\Query\Specialization\Model\Specialization\FacebookChannel;
use ITOffers\Offers\Application\Query\Specialization\Model\Specialization\Offers;
use ITOffers\Offers\Application\Query\Specialization\Model\Specialization\TwitterChannel;
use ITOffers\Offers\Application\Query\Specialization\Model\Specializations;
use ITOffers\Offers\Application\Query\Specialization\SpecializationQuery;

final class DbalSpecializationQuery implements SpecializationQuery
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function all() : Specializations
    {
        return new Specializations(
            ...\array_map(
                [$this, 'hydrateSpecialization'],
                $this->connection->fetchAll(
                    <<<SQL
                  SELECT 
                     s.id,
                     s.slug, 
                     s.facebook_channel_page_id as fb_page_id, 
                     s.facebook_channel_group_id as fb_group_id,
                     s.twitter_account_id,
                     s.twitter_screen_name,
                     (SELECT COUNT(*) FROM itof_job_offer WHERE specialization_id = s.id) as offers_count
                  FROM itof_specialization s 
                  ORDER BY offers_count desc
SQL
                )
            )
        );
    }

    public function allSlugs() : array
    {
        return \array_map(
            fn ($data) => $data['slug'],
            $this->connection->fetchAll(
                <<<SQL
          SELECT 
             s.slug, 
             (SELECT COUNT(*) FROM itof_job_offer WHERE specialization_id = s.id) as offers_count
          FROM itof_specialization s 
          ORDER BY offers_count desc
SQL
            )
        );
    }

    public function findBySlug(string $slug) : ?Specialization
    {
        $specialization = $this->connection->fetchAssoc(
            <<<SQL
            SELECT 
               s.id,
               s.slug, 
               s.facebook_channel_page_id as fb_page_id, 
               s.facebook_channel_group_id as fb_group_id,
               s.twitter_account_id,
               s.twitter_screen_name
            FROM itof_specialization s
            WHERE s.slug = :slug
SQL
            ,
            ['slug' => $slug]
        );

        if (!$specialization) {
            return null;
        }

        return $this->hydrateSpecialization($specialization);
    }

    private function hydrateSpecialization(array $data) : Specialization
    {
        // TODO: Optimize this, maybe try to merge this into main query or migrate to projections
        $offersData = $this->connection->fetchAssoc(
            <<<SQL
            SELECT 
               (SELECT COUNT(*) FROM itof_job_offer  WHERE specialization_id = :specializationId) as total_count,
               o.created_at
            FROM itof_job_offer o 
            WHERE o.specialization_id = :specializationId
            ORDER BY o.created_at DESC LIMIT 1
SQL
            ,
            ['specializationId' => $data['id']]
        );

        $offers = $offersData
            ?  Offers::create(
                $offersData['total_count'],
                DateTime::fromString($offersData['created_at'])
            )
            : Offers::noOffers();

        return new Specialization(
            $data['slug'],
            $offers,
            ($data['fb_page_id'])
                ? new FacebookChannel(
                    $data['fb_page_id'],
                    $data['fb_group_id']
                )
                : null,
            ($data['twitter_account_id'])
                ? new TwitterChannel(
                    $data['twitter_account_id'],
                    $data['twitter_screen_name']
                )
                : null
        );
    }
}
