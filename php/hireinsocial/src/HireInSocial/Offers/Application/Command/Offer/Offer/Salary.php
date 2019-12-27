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

namespace HireInSocial\Offers\Application\Command\Offer\Offer;

final class Salary
{
    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var bool
     */
    private $net;

    /**
     * @var string
     */
    private $periodType;

    public function __construct(int $min, int $max, string $currencyCode, bool $net, string $periodType)
    {
        $this->min = $min;
        $this->max = $max;
        $this->currencyCode = $currencyCode;
        $this->net = $net;
        $this->periodType = $periodType;
    }

    public function min() : int
    {
        return $this->min;
    }

    public function max() : int
    {
        return $this->max;
    }

    public function currencyCode() : string
    {
        return $this->currencyCode;
    }

    public function isNet() : bool
    {
        return $this->net;
    }

    public function periodType() : string
    {
        return $this->periodType;
    }
}
