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

namespace ITOffers\Tests\Offers\Application\Unit\User;

use Aeon\Calendar\Gregorian\GregorianCalendarStub;
use Aeon\Calendar\TimeUnit;
use DateInterval;
use ITOffers\Offers\Application\Exception\InvalidAssertionException;
use ITOffers\Offers\Application\User\ExtraOffer;
use ITOffers\Tests\Offers\Application\MotherObject\Offer\OfferMother;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ExtraOfferTest extends TestCase
{
    public function test_creating_extra_offer_with_negative_interval() : void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('Expires in interval can\'t be negative');

        $interval = new DateInterval("P1D");
        $interval->invert = 1;

        new ExtraOffer(Uuid::uuid4(), TimeUnit::fromDateInterval($interval), new GregorianCalendarStub());
    }

    public function test_using_expired_extra_offer() : void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('Extra offer expired');

        $extraOffer = new ExtraOffer(
            Uuid::uuid4(),
            $expiresIn = TimeUnit::fromDateInterval(new DateInterval("P1D")),
            $calendar = new GregorianCalendarStub()
        );

        $calendar->setNow($calendar->now()->addDays(2));
        $extraOffer->useFor(OfferMother::random(), $calendar);
    }

    public function test_using_extra_offer() : void
    {
        $offer = OfferMother::random();
        $extraOffer = new ExtraOffer(
            $offer->userId(),
            $expiresIn = TimeUnit::fromDateInterval(new DateInterval("P1D")),
            $calendar = new GregorianCalendarStub()
        );

        $extraOffer->useFor($offer, $calendar);

        $this->assertTrue($extraOffer->isUsed());
    }
}
