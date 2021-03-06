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

namespace App\Offers\Controller;

use ITOffers\ITOffersOnline;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LayoutController extends AbstractController
{
    private ITOffersOnline $itoffers;

    public function __construct(ITOffersOnline $itoffers)
    {
        $this->itoffers = $itoffers;
    }

    public function navbarAction(Request $request) : Response
    {
        return $this->render('@offers/layout/navbar.html.twig', [
            'logged_in' => (bool) $request->getSession()->get(SecurityController::USER_SESSION_KEY, false),
        ]);
    }

    public function headerAction(Request $request, ?string $specialization) : Response
    {
        return $this->render('@offers/layout/header.html.twig', [
            'specializationSlugs' => $this->itoffers->offers()->specializationQuery()->allSlugs(),
            'current' => $specialization,
        ]);
    }
}
