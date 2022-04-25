<?php

namespace Capco\UserBundle\Security\Http\Logout\Handler;

use Capco\AppBundle\Toggle\Manager;
use Capco\UserBundle\OpenID\OpenIDReferrerResolver;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class OpenIDLogoutHandler implements LogoutHandlerInterface
{
    private Manager $toggleManager;
    private ResourceOwnerInterface $resourceOwner;
    private RouterInterface $router;
    private OpenIDReferrerResolver $refererResolver;

    public function __construct(
        Manager $toggleManager,
        ResourceOwnerInterface $resourceOwner,
        RouterInterface $router,
        OpenIDReferrerResolver $referrerResolver
    ) {
        $this->toggleManager = $toggleManager;
        $this->resourceOwner = $resourceOwner;
        $this->router = $router;
        $this->refererResolver = $referrerResolver;
    }

    public function handle(
        RedirectResponseWithRequest $responseWithRequest
    ): RedirectResponseWithRequest {
        if ($this->toggleManager->isActive('disconnect_openid')) {
            $logoutURL = $this->resourceOwner->getOption('logout_url');

            $homepageUrl = $this->router->generate(
                'app_homepage',
                [],
                RouterInterface::ABSOLUTE_URL
            );

            $parameters = [];

            if ($responseWithRequest->getRequest()->query->get('ssoSwitchUser')) {
                $parameters = [
                    $this->refererResolver->getRefererParameterForLogout() =>
                        $homepageUrl . '/login/openid?_destination=' . $homepageUrl,
                ];
            } else {
                $parameters = [
                    $this->refererResolver->getRefererParameterForLogout() => $homepageUrl,
                ];
            }

            $responseWithRequest->setResponse(
                new RedirectResponse(
                    $logoutURL . '?' . http_build_query($parameters, '', '&') ?? '/'
                )
            );
        }

        return $responseWithRequest;
    }
}
