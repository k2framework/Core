<?php

namespace KumbiaPHP\Security\Auth;

use KumbiaPHP\Security\Auth\Token\TokenInterface;
use KumbiaPHP\Security\Auth\Provider\UserProviderInterface;

/**
 * Description of AuthManager
 *
 * @author manuel
 */
class AuthManager
{

    /**
     *
     * @var UserProviderInterface
     */
    protected $userProvider;

    function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    //put your code here
    public function autenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUser($token);

        if (TRUE !== $user->auth($token->getUser())) {
            throw new \Exception("no existe");
        }

        $token->setUser($user);
        $token->setAutenticated(TRUE);

        return TRUE;
    }

}