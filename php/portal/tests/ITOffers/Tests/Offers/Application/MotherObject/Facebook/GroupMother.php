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

namespace ITOffers\Tests\Offers\Application\MotherObject\Facebook;

use ITOffers\Offers\Application\Facebook\Group;

final class GroupMother
{
    public static function random() : Group
    {
        return new Group('123456789', 'Job Offers');
    }
}
