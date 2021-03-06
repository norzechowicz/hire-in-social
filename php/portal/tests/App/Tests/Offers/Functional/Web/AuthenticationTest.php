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

namespace App\Tests\Offers\Functional\Web;

use App\Tests\Functional\Web\WebTestCase;

final class AuthenticationTest extends WebTestCase
{
    public function test_redirect_to_login_page_when_want_to_add_new_offer_not_logged() : void
    {
        $client = static::createClient();
        $client->request('GET', $client->getContainer()->get('router')->generate('offer_new', ['specSlug' => 'php']));

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals($client->getContainer()->get('router')->generate('login'), $client->getResponse()->headers->get('location'));
    }
}
