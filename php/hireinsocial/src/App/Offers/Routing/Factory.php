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

namespace App\Offers\Routing;

use App\Offers\Controller\FacebookController;
use App\Offers\Controller\IndexController;
use App\Offers\Controller\OfferController;
use App\Offers\Controller\ReCaptchaController;
use App\Offers\Controller\SecurityController;
use App\Offers\Controller\SpecializationController;
use App\Offers\Controller\StaticController;
use App\Offers\Controller\UserController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class Factory
{
    public static function addRoutes(RouteCollectionBuilder $routes) : void
    {
        $routes->addRoute(
            new Route('/', ['_controller' => [IndexController::class, 'homeAction']]),
            'home'
        );
        $routes->addRoute(
            new Route('/faq', ['_controller' => [IndexController::class, 'faqAction']]),
            'faq'
        );
        $routes->addRoute(
            new Route('/recaptcha/verify', ['_controller' => [ReCaptchaController::class, 'verifyAction']]),
            'recaptcha_verify'
        );
        $routes->addRoute(
            new Route('/facebook/login', ['_controller' => [FacebookController::class, 'loginAction']]),
            'facebook_login'
        );
        $routes->addRoute(
            new Route('/facebook/logout', ['_controller' => [FacebookController::class, 'logoutAction']]),
            'facebook_logout'
        );
        $routes->addRoute(
            new Route('/facebook/login/success', ['_controller' => [FacebookController::class, 'loginSuccessAction']]),
            'facebook_login_success'
        );
    }

    public static function addLocalizedRoutes(RouteCollectionBuilder $routes, string $locale) : void
    {
        switch ($locale) {
            case 'pl-PL':
                $routes->addRoute(new Route('/oferty/dodaj', ['_controller' => [OfferController::class, 'postAction']]), 'offer_post');
                $routes->addRoute(new Route('/oferty/{specSlug}/dodaj', ['_controller' => [OfferController::class, 'newAction']]), 'offer_new');
                $routes->addRoute(new Route('/oferty/{specSlug}/dodaj/sukces', ['_controller' => [OfferController::class, 'successAction']]), 'offer_success');
                $routes->addRoute(new Route('/oferty/{specSlug}', ['_controller' => [SpecializationController::class, 'offersAction']]), 'specialization_offers');
                $routes->addRoute(new Route('/oferta-pracy/{offerSlug}/potwierdz-usuniecie', ['_controller' => [OfferController::class, 'removeConfirmationAction']]), 'offer_remove_confirmation');
                $routes->addRoute(new Route('/oferta-pracy/{offerSlug}/usun', ['_controller' => [OfferController::class, 'removeAction']]), 'offer_remove');
                $routes->addRoute(new Route('/oferta-pracy/{offerSlug}', ['_controller' => [OfferController::class, 'offerAction']]), 'offer');

                break;
            case 'en-US':
                $routes->addRoute(new Route('/offers/post', ['_controller' => [OfferController::class, 'postAction']]), 'offer_post');
                $routes->addRoute(new Route('/offers/{specSlug}/new', ['_controller' => [OfferController::class, 'newAction']]), 'offer_new');
                $routes->addRoute(new Route('/offers/{specSlug}/new/success', ['_controller' => [OfferController::class, 'successAction']]), 'offer_success');
                $routes->addRoute(new Route('/offers/{specSlug}', ['_controller' => [SpecializationController::class, 'offersAction']]), 'specialization_offers');
                $routes->addRoute(new Route('/job-offer//{offerSlug}/remove-confirmation', ['_controller' => [OfferController::class, 'removeConfirmationAction']]), 'offer_remove_confirmation');
                $routes->addRoute(new Route('/job-offer/{offerSlug}/remove', ['_controller' => [OfferController::class, 'removeAction']]), 'offer_remove');
                $routes->addRoute(new Route('/job-offer/{offerSlug}', ['_controller' => [OfferController::class, 'offerAction']]), 'offer');
                $routes->addRoute(new Route('/user/blocked', ['_controller' => [SecurityController::class, 'userBlockedAction']]), 'user_blocked');
                $routes->addRoute(new Route('/user/profile', ['_controller' => [UserController::class, 'profileAction']]), 'user_profile');
                $routes->addRoute(new Route('/terms-and-conditions', ['_controller' => [StaticController::class, 'termsAndConditionsAction']]), 'terms_and_conditions');
                $routes->addRoute(new Route('/privacy-policy', ['_controller' => [StaticController::class, 'privacyPolicyAction']]), 'privacy_policy');
                $routes->addRoute(new Route('/cookies-policy', ['_controller' => [StaticController::class, 'cookiesPolicyAction']]), 'cookies_policy');

                break;
            default:
                throw new \RuntimeException(sprintf('Unrecognized locale %s', $locale));
        }
    }
}
