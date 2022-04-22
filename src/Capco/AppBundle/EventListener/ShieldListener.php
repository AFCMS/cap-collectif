<?php

namespace Capco\AppBundle\EventListener;

use Capco\AppBundle\Toggle\Manager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Templating\EngineInterface;

class ShieldListener
{
    const AVAILABLE_ROUTES = [
      // Basics
      'app_get_api_token',
      'sonata_media_view',
      'sonata_media_download',
      'capco_metrics',
      '_wdt',

      // Login
      'capco_api_login_check',
      'facebook_login',
      'google_login',
      'api_login_check',
      'hwi_oauth_service_redirect',

      // API documentation
      'overblog_graphql_graphiql',
      'nelmio_api_doc_index',

      // Account confirmation
      'account_confirm_email',
      'account_confirm_new_email',

      // Registration
      'capco_app_api_users_postuser',

      // Password reset
      'fos_user_resetting_request',
      'fos_user_resetting_reset',
      'fos_user_resetting_send_email',
      'fos_user_resetting_check_email',
    ];
    protected $manager;
    protected $tokenStorage;
    protected $templating;

    public function __construct(Manager $manager, TokenStorageInterface $tokenStorage, EngineInterface $templating)
    {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->manager->isActive('shield_mode')) {
            return;
        }

        // If already authenticated, we don't need to show the shield
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof UserInterface) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->get('_route');

        if (\in_array($route, self::AVAILABLE_ROUTES, true)) {
            return;
        }

        if (false !== strpos($route, '_imagine')) {
            return;
        }

        $response = new Response(
          $this->templating->render('CapcoAppBundle:Default:shield.html.twig')
        );
        $event->setResponse($response);
    }
}
