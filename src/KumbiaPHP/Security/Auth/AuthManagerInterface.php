<?php

namespace KumbiaPHP\Security\Auth;

use KumbiaPHP\Security\Auth\Token\TokenInterface;

/**
 *
 * @author manuel
 */
interface AuthManagerInterface
{
    public function autenticate(TokenInterface $token);
}
