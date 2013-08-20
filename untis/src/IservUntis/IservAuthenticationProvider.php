<?php

namespace IservUntis;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IservAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user) {
            $authenticationToken = new IservUserToken($user->getRoles());
            $authenticationToken->setUser($user);

            return $authenticationToken;
        }

        throw new AuthenticationException('IServ authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof IservUserToken;
    }
}
