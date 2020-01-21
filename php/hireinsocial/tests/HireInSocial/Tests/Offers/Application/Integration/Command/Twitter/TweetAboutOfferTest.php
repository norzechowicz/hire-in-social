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

namespace HireInSocial\Tests\Offers\Application\Integration\Command\Twitter;

use HireInSocial\Offers\Application\Command\Specialization\SetTwitterChannel;
use HireInSocial\Offers\Application\Command\Twitter\TweetAboutOffer;
use HireInSocial\Offers\Application\Query\Twitter\Model\Tweet;
use HireInSocial\Tests\Offers\Application\Integration\OffersTestCase;

final class TweetAboutOfferTest extends OffersTestCase
{
    public function test_tweeting_about_offer() : void
    {
        $user = $this->systemContext->createUser();
        $specialization = $this->systemContext->createSpecialization($specialization = 'spec');
        $offer = $this->systemContext->createOffer($user->id(), $specialization->slug());
        $this->systemContext->offersFacade()->handle(new SetTwitterChannel(
            $specialization->slug(),
            'account_id',
            'account',
            'token',
            'secret',
        ));

        $this->systemContext->offersFacade()->handle(new TweetAboutOffer($offer->id()->toString(), 'This is offer message for facebook'));

        $this->assertInstanceOf(Tweet::class, $this->systemContext->offersFacade()->tweetsQuery()->findTweet($offer->id()->toString()));
    }
}
