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

namespace ITOffers\Component\Mailer;

final class Attachment
{
    private string $filepath;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }

    public function filepath() : string
    {
        return $this->filepath;
    }
}
