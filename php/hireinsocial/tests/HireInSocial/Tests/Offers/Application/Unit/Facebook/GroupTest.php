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

namespace HireInSocial\Tests\Offers\Application\Unit\Facebook;

use HireInSocial\Offers\Application\Exception\InvalidAssertionException;
use HireInSocial\Offers\Application\Facebook\Group;
use PHPUnit\Framework\TestCase;

final class GroupTest extends TestCase
{
    public function test_create_group_with_too_long_fb_id() : void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('Invalid FB Group ID');

        new Group(\str_repeat('1', 256), 'Job Offers');
    }

    public function test_create_group_with_too_short_fb_id() : void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('Invalid FB Group ID');

        new Group('1', 'Job Offers');
    }
}
