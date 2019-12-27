<?php

declare(strict_types=1);

/*
 * This file is part of the Hire in Social project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HireInSocial\Offers\Application\Offer;

use Cocur\Slugify\Slugify;
use Hashids\Hashids;
use HireInSocial\Offers\Application\System\Calendar;
use Ramsey\Uuid\UuidInterface;

class Slug
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $offerId;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    private function __construct(string $value, UuidInterface $offerId, \DateTimeImmutable $createdAt)
    {
        $this->slug = $value;
        $this->offerId = $offerId;
        $this->createdAt = $createdAt;
    }

    public static function from(Offer $offer, Calendar $calendar) : self
    {
        $hashids = new Hashids();
        $slugify = new Slugify();

        return new self(
            sprintf('%s-%s', $slugify->slugify($offer->position()->name() . ' ' . $offer->company()->name()), $hashids->encode(time() + \random_int(0, 5000))),
            $offer->id(),
            $calendar->currentTime()
        );
    }

    public function __toString() : string
    {
        return $this->slug;
    }
}