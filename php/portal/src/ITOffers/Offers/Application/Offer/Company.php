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

namespace ITOffers\Offers\Application\Offer;

use ITOffers\Offers\Application\Assertion;

final class Company
{
    private string $name;

    private string $url;

    private string $description;

    public function __construct(string $name, string $url, string $description)
    {
        Assertion::betweenLength($name, 3, 255);
        Assertion::url($url);
        Assertion::betweenLength($url, 3, 2_083);

        Assertion::betweenLength(\strip_tags($description), 20, 2_048);

        $this->name = $name;
        $this->url = $url;
        $this->description = $description;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function url() : string
    {
        return $this->url;
    }

    public function description() : string
    {
        return $this->description;
    }
}
