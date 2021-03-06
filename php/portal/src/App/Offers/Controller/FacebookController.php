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

use Facebook\Facebook;
use ITOffers\ITOffersOnline;
use ITOffers\Offers\Application\Command\User\FacebookConnect;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class FacebookController extends AbstractController
{
    use FacebookAccess;
    use RedirectAfterLogin;

    private ITOffersOnline $itoffers;

    private RouterInterface $router;

    private Facebook $facebook;

    private LoggerInterface $logger;

    public function __construct(
        ITOffersOnline $itoffers,
        RouterInterface $router,
        Facebook $facebook,
        LoggerInterface $logger
    ) {
        $this->itoffers = $itoffers;
        $this->facebook = $facebook;
        $this->logger = $logger;
        $this->router = $router;
    }

    public function loginAction(Request $request) : Response
    {
        if ($request->getSession()->has(SecurityController::USER_SESSION_KEY)) {
            return $this->redirectToRoute('home');
        }

        return $this->render('@offers/facebook/login.html.twig', [
            'facebook_login_url' => $this->facebook->getRedirectLoginHelper()->getLoginUrl(
                $this->generateUrl('facebook_login_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ['email']
            ),
        ]);
    }

    public function loginSuccessAction(Request $request) : Response
    {
        if (!$request->query->has('code')) {
            $this->logger->debug('Facebook login success action does not have code', [$request->query->all()]);

            return $this->redirectToRoute('facebook_login');
        }

        $this->logger->debug('Facebook login success action code exists.', ['code' => $request->query->get('code')]);

        $accessToken = $this->facebook->getOAuth2Client()->getAccessTokenFromCode(
            $request->query->get('code'),
            $this->generateUrl('facebook_login_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $fbUser = $this->getFbUser($this->facebook, $accessToken, $this->logger);

        if (!$fbUser['email']) {
            $this->clearFbPermissions($this->facebook, $fbUser['id'], $accessToken, $this->logger);
            $this->addFlash('warning', $this->renderView('@offers/alert/fb_email_required.txt'));

            return $this->redirectToRoute('facebook_login');
        }

        if ($user = $this->itoffers->offers()->userQuery()->findByEmail($fbUser['email'])) {
            if ($user->fbAppId() !== $fbUser['id']) {
                $this->addFlash('warning', $this->renderView('@offers/alert/fb_email_already_used.txt'));

                return $this->redirectToRoute('facebook_login');
            }
        }

        $this->itoffers->offers()->handle(new FacebookConnect($fbUser['id'], $fbUser['email']));

        $user = $this->itoffers->offers()->userQuery()->findByFacebook($fbUser['id']);

        if ($user->isBlocked()) {
            return $this->redirectToRoute('user_blocked');
        }

        $request->getSession()->set(SecurityController::USER_SESSION_KEY, $user->id());

        if ($this->hasRedirection($request->getSession())) {
            return $this->generateRedirection($request->getSession(), $this->router);
        }

        return $this->redirectToRoute('home');
    }
}
