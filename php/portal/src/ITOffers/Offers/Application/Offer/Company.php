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
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $name, string $url, string $description)
    {
        Assertion::betweenLength($name, 3, 255);
        Assertion::url($url);
        Assertion::betweenLength($url, 1, 2083);

        Assertion::betweenLength($description, 10, 2048);

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