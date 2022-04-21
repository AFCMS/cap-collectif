<?php

namespace Capco\AppBundle\Saml;

use Hslavich\SimplesamlphpBundle\Security\Core\Authentication\Token\SamlToken;
use Hslavich\SimplesamlphpBundle\Security\Core\User\SamlUserInterface;
use Hslavich\SimplesamlphpBundle\Exception\MissingSamlAuthAttributeException;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Capco\AppBundle\Toggle\Manager;

class SamlAuthenticator implements SimplePreAuthenticatorInterface
{
    protected $samlAuth;
    protected $authAttribute;
    protected $httpUtils;
    protected $toggleManager;

    public function __construct(SimpleSAML_Auth_Simple $samlAuth, string $authAttribute, HttpUtils $httpUtils, Manager $toggleManager)
    {
        $this->samlAuth = $samlAuth;
        $this->authAttribute = $authAttribute;
        $this->httpUtils = $httpUtils;
        $this->toggleManager = $toggleManager;
    }

    public function createToken(Request $request, $providerKey)
    {
        if (!$this->toggleManager->isActive('login_saml')) {
            return null;
        }

        $isOnLoginUrl = $this->httpUtils->checkRequestPath($request, '/login-saml');
        if (!$isOnLoginUrl && !$this->samlAuth->isAuthenticated()) {
            return null; // skip saml auth, to let users browse anonymously
        }

        $this->samlAuth->requireAuth(); // force the user to login with SAML
        $attributes = $this->samlAuth->getAttributes();

        if (!array_key_exists($this->authAttribute, $attributes)) {
            throw new MissingSamlAuthAttributeException(
                sprintf("Attribute '%s' was not found in SAMLResponse", $this->authAttribute)
            );
        }

        $username = $attributes[$this->authAttribute][0];

        $token = new SamlToken($username);
        $token->setAttributes($attributes);

        return $token;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $username = $token->getUsername();
        $user = $userProvider->loadUserByUsername($username);

        if ($user instanceof SamlUserInterface) {
            $user->setSamlAttributes($token->getAttributes());
        }

        $authenticatedToken = new SamlToken($user, $user->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof SamlToken;
    }
}
