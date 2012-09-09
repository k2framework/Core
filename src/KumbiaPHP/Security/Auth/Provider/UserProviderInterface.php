<?php

namespace KumbiaPHP\Security\Auth\Provider;

use KumbiaPHP\Security\Auth\Token\TokenInterface;
use KumbiaPHP\Security\Auth\User\UserInterface;

/**
 *
 * @author manuel
 */
interface UserProviderInterface
{

    /**
     *  @return UserInterface
     */
    public function loadUser(TokenInterface $token);
}
