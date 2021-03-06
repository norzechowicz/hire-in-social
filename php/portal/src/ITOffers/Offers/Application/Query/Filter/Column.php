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

namespace ITOffers\Offers\Application\Query\Filter;

final class Column
{
    private string $column;

    private string $direction;

    private function __construct()
    {
    }

    public static function asc(string $column) : self
    {
        $sort = new self();
        $sort->column = $column;
        $sort->direction = 'ASC';

        return $sort;
    }

    public static function desc(string $column) : self
    {
        $sort = new self();
        $sort->column = $column;
        $sort->direction = 'DESC';

        return $sort;
    }

    public function is(string $name) : bool
    {
        return \mb_strtolower($this->column) === \mb_strtolower($name);
    }

    public function column() : string
    {
        return $this->column;
    }

    public function direction() : string
    {
        return $this->direction;
    }
}
