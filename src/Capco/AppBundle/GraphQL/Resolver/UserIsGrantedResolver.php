<?php

namespace Capco\AppBundle\GraphQL\Resolver;

use Capco\UserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class UserIsGrantedResolver
{
    protected $tokenStorage;
    protected $logger;

    public function __construct(TokenStorage $tokenStorage, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * if $user is tped, I receive an error 500. But I want a graphql error, so I need to check the instance of $user.
     *
     */
    public function isGranted($user, $userRequest = null, $context = null, array $roleRequest = ['ROLE_ADMIN']): bool
    {
        if ($context && isset($context['disable_acl'])) {
            return true;
        }
        if (!$user instanceof User) {
            return false;
        }
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }

        foreach ($roleRequest as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }


        if ($userRequest && $userRequest instanceof User) {
            if ($user->hasRole('ROLE_USER') && $user->getId() === $userRequest->getId()) {
                return true;
            }

            return false;
        }

        if ($user->hasRole('ROLE_USER') && $user->getId() === $token->getUser()->getId()) {
            return true;
        }

        $this->logger->warning(
            __METHOD__.' : User with id '.$user->getId().' try to get information about user with id '.$token->getUser(
            )->getId()
        );

        return false;
    }
}
