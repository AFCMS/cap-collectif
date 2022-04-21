<?php

namespace Capco\UserBundle\Security\Http\Logout;

use Capco\AppBundle\Toggle\Manager;
use Capco\UserBundle\MonCompteParis\OpenAmClient;
use SimpleSAML\Auth\Simple;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    protected $samlAuth;
    protected $router;
    protected $toggleManager;
    protected $client;

    public function __construct(Simple $samlAuth, Router $router, Manager $toggleManager, OpenAmClient $client)
    {
        $this->samlAuth = $samlAuth;
        $this->router = $router;
        $this->toggleManager = $toggleManager;
        $this->client = $client;
    }

    public function onLogoutSuccess(Request $request)
    {
        $returnTo = $request->headers->get('referer', '/');
        $request->getSession()->invalidate();

        if ($this->toggleManager->isActive('login_saml')) {
            $this->samlAuth->logout($returnTo);
        }

        $response = new RedirectResponse($returnTo);

        if ($this->toggleManager->isActive('login_paris')) {
            $this->client->setCookie($request->cookies->get(OpenAmClient::COOKIE_NAME));
            $this->client->logoutUser();
            $response->headers->clearCookie(OpenAmClient::COOKIE_NAME, '/', OpenAmClient::COOKIE_DOMAIN);
        }

        return $response;
    }
}
