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

namespace App\Notifications\Controller;

use ITOffers\ITOffersOnline;
use ITOffers\Tests\Notifications\Application\MotherObject\OfferMother;
use Pelago\Emogrifier\CssInliner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EmailController extends AbstractController
{
    private ITOffersOnline $itoffers;

    private ParameterBagInterface $parameterBag;

    public function __construct(ITOffersOnline $itoffers, ParameterBagInterface $parameterBag)
    {
        $this->itoffers = $itoffers;
        $this->parameterBag = $parameterBag;
    }

    public function listAction(Request $request) : Response
    {
        return $this->render('@notifications/email/list.html.twig', [
            'emails' => [
                [
                    'name' => 'Offer Posted',
                    'type' => 'offer_posted',
                    'subject' => $this->renderView(
                        '@notifications/email/offer/posted_subject.txt.twig',
                        ['offer' => OfferMother::random()]
                    ),
                ],
                [
                    'name' => 'Extra Offers Added',
                    'type' => 'user_extra_offers_added',
                    'subject' => $this->renderView(
                        '@notifications/email/user/extra_offers_added_subject.txt.twig',
                        []
                    ),
                ],
                [
                    'name' => 'Offer Auto Renews Added',
                    'type' => 'offer_auto_renews_added',
                    'subject' => $this->renderView(
                        '@notifications/email/user/offer_auto_renews_added_subject.txt.twig',
                        []
                    ),
                ],
            ],
        ]);
    }

    public function previewAction(Request $request, string $emailName) : Response
    {
        switch (\mb_strtolower($emailName)) {
            case 'offer_posted':
                return new Response(
                    CssInliner::fromHtml(
                        $this->renderView(
                            '@notifications/email/offer/posted_body.html.twig',
                            ['offer' => OfferMother::random()]
                        )
                    )
                    ->inlineCss()
                    ->renderBodyContent()
                );
            case 'user_extra_offers_added':
                return new Response(
                    CssInliner::fromHtml(
                        $this->renderView(
                            '@notifications/email/user/extra_offers_added_body.html.twig',
                            ['expiresInDays' => \random_int(30, 60), 'amount' => \random_int(1, 10)]
                        )
                    )
                        ->inlineCss()
                        ->renderBodyContent()
                );
            case 'offer_auto_renews_added':
                return new Response(
                    CssInliner::fromHtml(
                        $this->renderView(
                            '@notifications/email/user/offer_auto_renews_added_body.html.twig',
                            ['expiresInDays' => \random_int(30, 60), 'amount' => \random_int(1, 10)]
                        )
                    )
                        ->inlineCss()
                        ->renderBodyContent()
                );
            default:
                $this->createNotFoundException(\sprintf('Email %s does not exist', $emailName));
        }

        return new Response('', 422);
    }
}
