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

namespace ITOffers\Offers\Application\Facebook;

use ITOffers\Offers\Application\Assertion;

final class Group
{
    private string $fbId;

    private string $name;

    public function __construct(string $fbId, string $name)
    {
        Assertion::betweenLength($fbId, 3, 255, 'Invalid FB Group ID');
        Assertion::betweenLength($name, 3, 512, 'Invalid FB Group Name');

        $this->fbId = $fbId;
        $this->name = $name;
    }

    public function fbId() : string
    {
        return $this->fbId;
    }

    public function name() : string
    {
        return $this->name;
    }
}
