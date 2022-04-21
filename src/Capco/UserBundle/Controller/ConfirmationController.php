<?php

namespace Capco\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfirmationController extends Controller
{
    /**
     * @Route("/email-confirmation/{token}", defaults={"_feature_flags" = "registration"})
     */
    public function emailAction($token)
    {
        $manager = $this->container->get('fos_user.user_manager');
        $session = $this->container->get('session');
        $user = $manager->findUserByConfirmationToken($token);
        $response = new RedirectResponse($this->container->get('router')->generate('app_homepage'));

        if (!$user) {
            $session->getFlashBag()->set('sonata_user_success', 'global.alert.already_email_confirmed');
            return $response;
        }

        $user->setEnabled(true);
        $user->setExpired(false);
        $user->setExpiresAt(null);
        $user->setLastLogin(new \DateTime());

        $hasRepublishedContributions = $this->get('capco.contribution.manager')->republishContributions($user);

        // if user has been created via API he has no password yet.
        // That's why we create a reset password request to let him chose a password
        if ($user->getPassword() === null) {
            $user->setPasswordRequestedAt(new \DateTime());
            $manager->updateUser($user);

            return $this->redirectToRoute('fos_user_resetting_reset', ['token' => $user->getConfirmationToken()]);
        }

        $user->setConfirmationToken(null);
        $manager->updateUser($user);

        $this->get('fos_user.security.login_manager')->loginUser(
            $this->container->getParameter('fos_user.firewall_name'),
            $user,
            $response
        );

        if ($hasRepublishedContributions) {
            $session->getFlashBag()->set('sonata_user_success', 'global.alert.email_confirmed_with_republish');
        } else {
            $session->getFlashBag()->set('sonata_user_success', 'global.alert.email_confirmed');
        }

        return $response;
    }

    /**
     * @Route("/account/new_email_confirmation/{token}")
     */
    public function newEmailAction($token)
    {
        $manager = $this->container->get('fos_user.user_manager');
        $redirectResponse = new RedirectResponse($this->container->get('router')->generate('app_homepage'));
        $user = $this
          ->container->get('capco.user.repository')
          ->findUserByNewEmailConfirmationToken($token)
        ;

        if (!$user) {
           return $redirectResponse;
        }

        $user->setEmail($user->getNewEmailToConfirm());
        $user->setNewEmailConfirmationToken(null);
        $user->setNewEmailToConfirm(null);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->get('fos_user.security.login_manager')->loginUser(
            $this->container->getParameter('fos_user.firewall_name'),
            $user,
            $redirectResponse
        );

        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('global.alert.new_email_confirmed'));

        return $redirectResponse;
    }
}
