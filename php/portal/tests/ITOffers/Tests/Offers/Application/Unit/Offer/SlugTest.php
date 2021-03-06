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

namespace ITOffers\Tests\Offers\Application\Unit\Offer;

use Aeon\Calendar\Gregorian\GregorianCalendarStub;
use ITOffers\Offers\Application\Offer\Slug;
use ITOffers\Tests\Offers\Application\MotherObject\Offer\OfferMother;
use PHPStan\Testing\TestCase;

final class SlugTest extends TestCase
{
    public function test_creating_offer_slug() : void
    {
        $order = OfferMother::withName('PHP Developer', 'Super Company');

        $this->assertRegExp(
            '/^(intern|junior|mid|senior|expert)-php-developer-super-company-(.)+/',
            (string) Slug::from($order, new GregorianCalendarStub())
        );
    }
}
