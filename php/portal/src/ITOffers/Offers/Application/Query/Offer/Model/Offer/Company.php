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

namespace ITOffers\Offers\Application\Query\Offer\Model\Offer;

final class Company
{
    private string $name;

    private string $url;

    private string $description;

    private ?CompanyLogo $companyLogo = null;

    public function __construct(string $name, string $url, string $description, ?CompanyLogo $companyLogo)
    {
        $this->name = $name;
        $this->url = $url;
        $this->description = $description;
        $this->companyLogo = $companyLogo;
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

    public function logo() : ?CompanyLogo
    {
        return $this->companyLogo;
    }
}
