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

namespace ITOffers\Tests\Offers\Application\MotherObject\Command\Specialization;

use ITOffers\Offers\Application\Command\Specialization\CreateSpecialization;

final class CreateSpecializationMother
{
    public static function create(string $slug) : CreateSpecialization
    {
        return new CreateSpecialization(
            $slug
        );
    }

    public static function random() : CreateSpecialization
    {
        return self::create('slug');
    }
}
