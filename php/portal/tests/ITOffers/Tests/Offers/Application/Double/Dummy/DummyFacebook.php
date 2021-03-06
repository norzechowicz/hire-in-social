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

namespace ITOffers\Tests\Offers\Application\Double\Dummy;

use ITOffers\Offers\Application\Facebook\Draft;
use ITOffers\Offers\Application\Facebook\Facebook;
use ITOffers\Offers\Application\Facebook\Group;
use ITOffers\Offers\Application\Facebook\Page;

final class DummyFacebook implements Facebook
{
    public function getUserAppId(string $accessToken) : string
    {
        return \md5(\uniqid('facebook_user_app_id'));
    }

    public function userExists(string $userAppId) : bool
    {
        return true;
    }

    public function postToGroupAsPage(Draft $post, Group $group, Page $page) : string
    {
        return \md5(\uniqid('facebook_post_id'));
    }
}
