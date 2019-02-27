<?php

declare(strict_types=1);

namespace App\Tests\Functional\Symfony;

use Faker\Factory;
use HireInSocial\Application\Query\Offer\OfferFilter;
use HireInSocial\Application\Query\Offer\OfferQuery;
use App\Tests\Functional\WebTestCase;
use HireInSocial\Tests\Application\MotherObject\Command\Offer\PostOfferMother;
use App\Controller\FacebookController;

final class OfferTest extends WebTestCase
{
    private $specialization = 'php';

    public function setUp()
    {
        parent::setUp();

        $this->systemContext->createSpecialization($this->specialization);
    }

    public function test_new_offer_page()
    {
        $user = $this->systemContext->createUser();

        $client = static::createClient();
        $client->getContainer()
            ->get('session')
            ->set(FacebookController::USER_SESSION_KEY, (string) $user->id());

        $crawler = $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('offer_post')
        );

        $this->assertEquals(1, $crawler->filter('a[data-post-offer]')->count());
    }

    public function test_success_page_after_posting_offer()
    {
        $user = $this->systemContext->createUser();

        $client = static::createClient();
        $client->getContainer()
            ->get('session')
            ->set(FacebookController::USER_SESSION_KEY, (string) $user->id());

        $crawler = $client->request(
            'GET',
            $client->getContainer()->get('router')->generate('offer_new', ['specSlug' => $this->specialization])
        );

        $faker = Factory::create();
        $form = $crawler->filter('form[name="offer"]')->form([
            'offer[company][name]' => 'Company name',
            'offer[company][url]' => 'http://company.com',
            'offer[company][description]' => $faker->text(512),
            'offer[position][name]' => 'Software Developer',
            'offer[position][description]' => $faker->text(1024),
            'offer[salary][min]' => 1000,
            'offer[salary][max]' => 5000,
            'offer[salary][currency]' => 'USD',
            'offer[salary][net]' => 1,
            'offer[contract]' => 'B2B',
            'offer[location][remote]' => 1,
            'offer[location][name]' => 'Cracow',
            'offer[description][requirements]' => $faker->text(1024),
            'offer[description][benefits]' => $faker->text(1024),
            'offer[contact][email]' => $faker->email,
            'offer[contact][name]' => $faker->name,
            'offer[contact][phone]' => '+12123123123',
            'offer[channels][facebook_group]' => 1,
            'offer[_token]' => $client->getContainer()->get('security.csrf.token_manager')->getToken('new_offer'),
        ]);

        $form['offer[channels][facebook_group]']->untick();

        $client->submit($form);

        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('.alert-success')->count());
    }

    public function test_offer_details_page()
    {
        $client = static::createClient();
        $user = $this->systemContext->createUser();
        $this->systemContext->system()->handle(PostOfferMother::random($user->id(), $this->specialization));

        $offer = $this->system()->query(OfferQuery::class)->findAll(OfferFilter::allFor($this->specialization))->first();

        $client->request('GET', $client->getContainer()->get('router')->generate('offer', ['offerSlug' => $offer->slug()]));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}