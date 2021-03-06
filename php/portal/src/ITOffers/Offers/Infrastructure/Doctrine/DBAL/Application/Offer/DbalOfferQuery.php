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

namespace ITOffers\Offers\Infrastructure\Doctrine\DBAL\Application\Offer;

use Aeon\Calendar\Gregorian\Calendar;
use Aeon\Calendar\Gregorian\DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use ITOffers\Offers\Application\Query\Offer\Model\Offer;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Company;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\CompanyLogo;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Contact;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Contract;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Description;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Description\Requirements;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Description\Requirements\Skill;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Location;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\OfferPDF;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Parameters;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Position;
use ITOffers\Offers\Application\Query\Offer\Model\Offer\Salary;
use ITOffers\Offers\Application\Query\Offer\Model\Offers;
use ITOffers\Offers\Application\Query\Offer\Model\OfferSeniorityLevel;
use ITOffers\Offers\Application\Query\Offer\Model\OffersSeniorityLevel;
use ITOffers\Offers\Application\Query\Offer\OfferFilter;
use ITOffers\Offers\Application\Query\Offer\OfferQuery;
use Ramsey\Uuid\Uuid;

final class DbalOfferQuery implements OfferQuery
{
    private Connection $connection;

    private Calendar $calendar;

    public function __construct(Connection $connection, Calendar $calendar)
    {
        $this->connection = $connection;
        $this->calendar = $calendar;
    }

    public function total() : int
    {
        return (int) $this->connection->fetchColumn('SELECT COUNT(*) FROM itof_job_offer o WHERE o.removed_at IS NULL');
    }

    public function findAll(OfferFilter $filter) : Offers
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('
                o.*, 
                op.path as offer_pdf, 
                ocl.path as offer_company_logo, 
                os.slug, 
                s.slug as specialization_slug, 
                CAST(o.salary->>\'max\' as INTEGER) as salary_max,
                (SELECT COUNT(a.*) FROM itof_job_offer_application a WHERE o.id = a.offer_id) as applications_count
            ');

        $this->prepareFindQueryBuilder($filter, $queryBuilder);

        $offersData = $queryBuilder->execute()
            ->fetchAll();

        return new Offers(...\array_map(
            [$this, 'hydrateOffer'],
            $offersData
        ));
    }

    public function offersSeniorityLevels(OfferFilter $filter) : OffersSeniorityLevel
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('
                o.position_seniority_level,
                COUNT(o.*)
            ');

        $this->prepareFindQueryBuilder($filter, $queryBuilder);

        $queryBuilder->groupBy('o.position_seniority_level');
        $queryBuilder->orderBy('o.position_seniority_level', 'ASC');

        $offersCountData = $queryBuilder->execute()
            ->fetchAll();

        return new OffersSeniorityLevel(...\array_map(
            fn (array $data) => new OfferSeniorityLevel($data['position_seniority_level'], $data['count']),
            $offersCountData
        ));
    }

    public function count(OfferFilter $filter) : int
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from('itof_job_offer', 'o')
            ->leftJoin('o', 'itof_specialization', 's', 'o.specialization_id = s.id')
            ->where('o.removed_at IS NULL');

        if ($filter->specialization()) {
            $queryBuilder->andWhere('s.slug = :specializationSlug');
        }

        if ($filter->remote()) {
            $queryBuilder->andWhere('o.location_remote = true');
        }

        if ($filter->withSalary()) {
            $queryBuilder->andWhere('o.salary IS NOT NULL');
        }

        if ($filter->seniorityLevel()) {
            $queryBuilder->andWhere('o.position_seniority_level = :seniorityLevel');
            $queryBuilder->setParameter('seniorityLevel', $filter->seniorityLevel());
        }

        if ($filter->userId()) {
            $queryBuilder->andWhere('o.user_id = :userId');
            $queryBuilder->setParameter('userId', $filter->userId());
        }

        if ($filter->afterOfferId()) {
            $queryBuilder->andWhere('o.id <> :afterOfferId')
                ->setParameter('afterOfferId', $filter->afterOfferId());
            $queryBuilder->andWhere('o.created_at <= (SELECT created_at FROM itof_job_offer WHERE id = :afterOfferId)');
        }

        if ($filter->createdInLastDays()) {
            $queryBuilder->andWhere('o.created_at >= :sinceDate')
                ->setParameter(
                    'sinceDate',
                    $this->calendar->now()
                        ->modify(\sprintf('-%d days', $filter->createdInLastDays()))
                        ->format($this->connection->getDatabasePlatform()->getDateTimeFormatString())
                );
        }

        $queryBuilder->setParameter('specializationSlug', $filter->specialization());

        return (int) $queryBuilder
            ->execute()
            ->fetchColumn();
    }

    public function findById(string $id) : ?Offer
    {
        $offerData = $this->connection->createQueryBuilder()
            ->select('
                o.*, 
                op.path as offer_pdf, 
                ocl.path as offer_company_logo,
                os.slug, 
                s.slug as specialization_slug,
                (SELECT COUNT(a.*) FROM itof_job_offer_application a WHERE o.id = a.offer_id) as applications_count
            ')
            ->from('itof_job_offer_slug', 'os')
            ->leftJoin('os', 'itof_job_offer', 'o', 'os.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_pdf', 'op', 'op.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_company_logo', 'ocl', 'ocl.offer_id = o.id')
            ->leftJoin('o', 'itof_specialization', 's', 'o.specialization_id = s.id')
            ->where('o.id = :id')
            ->andWhere('o.removed_at IS NULL')
            ->setParameters(
                [
                    'id' => $id,
                ]
            )->execute()
            ->fetch();

        if (!$offerData) {
            return null;
        }

        return $this->hydrateOffer($offerData);
    }

    public function findByEmailHash(string $emailHah) : ?Offer
    {
        $offerData = $this->connection->createQueryBuilder()
            ->select('
                o.*, 
                op.path as offer_pdf, 
                ocl.path as offer_company_logo,
                os.slug, 
                s.slug as specialization_slug,
                (SELECT COUNT(a.*) FROM itof_job_offer_application a WHERE o.id = a.offer_id) as applications_count
            ')
            ->from('itof_job_offer_slug', 'os')
            ->leftJoin('os', 'itof_job_offer', 'o', 'os.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_pdf', 'op', 'op.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_company_logo', 'ocl', 'ocl.offer_id = o.id')
            ->leftJoin('o', 'itof_specialization', 's', 'o.specialization_id = s.id')
            ->where('o.email_hash = :emailHash')
            ->andWhere('o.removed_at IS NULL')
            ->setParameters(
                [
                    'emailHash' => $emailHah,
                ]
            )->execute()
            ->fetch();

        if (!$offerData) {
            return null;
        }

        return $this->hydrateOffer($offerData);
    }

    public function findBySlug(string $slug) : ?Offer
    {
        $offerData = $this->connection->createQueryBuilder()
            ->select('
                o.*, 
                op.path as offer_pdf, 
                ocl.path as offer_company_logo,
                os.slug, 
                s.slug as specialization_slug,
                (SELECT COUNT(a.*) FROM itof_job_offer_application a WHERE o.id = a.offer_id) as applications_count
            ')
            ->from('itof_job_offer_slug', 'os')
            ->leftJoin('os', 'itof_job_offer', 'o', 'os.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_pdf', 'op', 'op.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_company_logo', 'ocl', 'ocl.offer_id = o.id')
            ->leftJoin('o', 'itof_specialization', 's', 'o.specialization_id = s.id')
            ->where('os.slug = :offerSlug')
            ->andWhere('o.removed_at IS NULL')
            ->setParameters(
                [
                    'offerSlug' => $slug,
                ]
            )->execute()
            ->fetch();

        if (!$offerData) {
            return null;
        }

        return $this->hydrateOffer($offerData);
    }

    public function findOneAfter(Offer $offer) : ?Offer
    {
        $offerData = $this->connection->createQueryBuilder()
            ->select('
                o.*, 
                op.path as offer_pdf, 
                ocl.path as offer_company_logo,
                os.slug, 
                s.slug as specialization_slug,
                (SELECT COUNT(a.*) FROM itof_job_offer_application a WHERE o.id = a.offer_id) as applications_count
            ')
            ->from('itof_job_offer', 'o')
            ->leftJoin('o', 'itof_specialization', 's', 'o.specialization_id = s.id')
            ->leftJoin('o', 'itof_job_offer_slug', 'os', 'os.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_company_logo', 'ocl', 'ocl.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_pdf', 'op', 'op.offer_id = o.id')
            ->where('s.slug = :specializationSlug')
            ->andWhere('o.created_at < (SELECT created_at FROM itof_job_offer WHERE id = :previousOfferId)')
            ->andWhere('o.id <> :previousOfferId')
            ->andWhere('o.removed_at IS NULL')
            ->orderBy('o.created_at', 'DESC')
            ->setMaxResults(1)
            ->setParameters(
                [
                    'specializationSlug' => $offer->specializationSlug(),
                    'previousOfferId' => $offer->id()->toString(),
                ]
            )->execute()
            ->fetch();

        if (!$offerData) {
            return null;
        }

        return $this->hydrateOffer($offerData);
    }

    public function findOneBefore(Offer $offer) : ?Offer
    {
        $offerData = $this->connection->createQueryBuilder()
            ->select('
                o.*, 
                op.path as offer_pdf,
                ocl.path as offer_company_logo,
                os.slug, 
                s.slug as specialization_slug,
                (SELECT COUNT(a.*) FROM itof_job_offer_application a WHERE o.id = a.offer_id) as applications_count
            ')
            ->from('itof_job_offer', 'o')
            ->leftJoin('o', 'itof_specialization', 's', 'o.specialization_id = s.id')
            ->leftJoin('o', 'itof_job_offer_slug', 'os', 'os.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_company_logo', 'ocl', 'ocl.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_pdf', 'op', 'op.offer_id = o.id')
            ->where('s.slug = :specializationSlug')
            ->andWhere('o.created_at > (SELECT created_at FROM itof_job_offer WHERE id = :previousOfferId)')
            ->andWhere('o.id <> :previousOfferId')
            ->andWhere('o.removed_at IS NULL')
            ->orderBy('o.created_at', 'ASC')
            ->setMaxResults(1)
            ->setParameters(
                [
                    'specializationSlug' => $offer->specializationSlug(),
                    'previousOfferId' => $offer->id()->toString(),
                ]
            )->execute()
            ->fetch();

        if (!$offerData) {
            return null;
        }

        return $this->hydrateOffer($offerData);
    }

    private function hydrateOffer(array $offerData) : Offer
    {
        $salary = isset($offerData['salary']) ? \json_decode($offerData['salary'], true, 512, JSON_THROW_ON_ERROR) : null;
        $skills = isset($offerData['description_requirements_skills']) ? \json_decode($offerData['description_requirements_skills'], true, 512, JSON_THROW_ON_ERROR) : null;
        $offerPDF = isset($offerData['offer_pdf']) ? new OfferPDF($offerData['offer_pdf']) : null;
        $companyLogo = isset($offerData['offer_company_logo']) ? new CompanyLogo($offerData['offer_company_logo']) : null;

        return new Offer(
            Uuid::fromString($offerData['id']),
            $offerData['slug'],
            $offerData['email_hash'],
            $offerData['locale_code'],
            Uuid::fromString($offerData['user_id']),
            $offerData['specialization_slug'],
            DateTime::fromString($offerData['created_at']),
            new Parameters(
                new Company(
                    $offerData['company_name'],
                    $offerData['company_url'],
                    $offerData['company_description'],
                    $companyLogo
                ),
                isset($offerData['contact_url'])
                    ? Contact::externalSource($offerData['contact_url'])
                    : Contact::recruiter($offerData['contact_email'], $offerData['contact_name'], $offerData['contact_phone']),
                new Contract($offerData['contract_type']),
                new Description(
                    $offerData['description_technology_stack'],
                    $offerData['description_benefits'],
                    new Requirements(
                        $offerData['description_requirements_description'],
                        ...$skills
                            ? \array_map(
                                fn (array $skillData) => new Skill(
                                    $skillData['name'],
                                    $skillData['required'],
                                    $skillData['experience_years'],
                                ),
                                $skills
                            ) : []
                    )
                ),
                new Location(
                    $offerData['location_remote'],
                    $offerData['location_country_code'],
                    $offerData['location_city'],
                    $offerData['location_address'],
                    $offerData['location_lat'] ? (float) $offerData['location_lat'] : null,
                    $offerData['location_lng'] ? (float) $offerData['location_lng'] : null
                ),
                new Position(
                    $offerData['position_seniority_level'],
                    $offerData['position_name']
                ),
                ($salary)
                    ? new Salary(
                        $salary['min'],
                        $salary['max'],
                        $salary['currency_code'],
                        $salary['net'],
                        $salary['period_type']
                    )
                    : null
            ),
            $offerData['applications_count'],
            $offerPDF
        );
    }

    /**
     * @param OfferFilter $filter
     * @param QueryBuilder $queryBuilder
     * @throws DBALException
     */
    private function prepareFindQueryBuilder(OfferFilter $filter, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from('itof_job_offer', 'o')
            ->leftJoin('o', 'itof_specialization', 's', 'o.specialization_id = s.id')
            ->leftJoin('o', 'itof_job_offer_slug', 'os', 'os.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_pdf', 'op', 'op.offer_id = o.id')
            ->leftJoin('o', 'itof_job_offer_company_logo', 'ocl', 'ocl.offer_id = o.id')
            ->where('o.removed_at IS NULL');

        if ($filter->specialization()) {
            $queryBuilder->andWhere('s.slug = :specializationSlug');
        }

        if ($filter->remote()) {
            $queryBuilder->andWhere('o.location_remote = true');
        }

        if ($filter->withSalary()) {
            $queryBuilder->andWhere('o.salary IS NOT NULL');
        }

        if ($filter->userId()) {
            $queryBuilder->andWhere('o.user_id = :userId');
            $queryBuilder->setParameter('userId', $filter->userId());
        }

        if ($filter->seniorityLevel()) {
            $queryBuilder->andWhere('o.position_seniority_level = :seniorityLevel');
            $queryBuilder->setParameter('seniorityLevel', $filter->seniorityLevel());
        }

        if ($filter->afterOfferId()) {
            $queryBuilder->andWhere('o.id <> :afterOfferId')
                ->setParameter('afterOfferId', $filter->afterOfferId());
            $queryBuilder->andWhere('o.created_at <= (SELECT created_at FROM itof_job_offer WHERE id = :afterOfferId)');
        }

        if ($filter->createdInLastDays()) {
            $queryBuilder->andWhere('o.created_at >= :sinceDate')
                ->setParameter(
                    'sinceDate',
                    $this->calendar->now()
                        ->modify(\sprintf('-%d days', $filter->createdInLastDays()))
                        ->format($this->connection->getDatabasePlatform()->getDateTimeFormatString())
                );
        }

        $queryBuilder->setMaxResults($filter->limit());

        if ($filter->offset()) {
            $queryBuilder->setFirstResult($filter->offset());
        }

        if ($filter->isSorted()) {
            foreach ($filter->sortByColumns() as $column) {
                if ($column->is(OfferFilter::COLUMN_SALARY)) {
                    $queryBuilder->addOrderBy('salary_max', $column->direction());
                }

                if ($column->is(OfferFilter::COLUMN_CREATED_AT)) {
                    $queryBuilder->addOrderBy('o.created_at', $column->direction());
                }
            }
        } else {
            $queryBuilder->orderBy('o.created_at', 'DESC');
        }

        $queryBuilder->setParameter('specializationSlug', $filter->specialization());
    }
}
