<?php

namespace K2\Security\Auth\User;

use K2\Security\Config\Reader;
use K2\Security\Auth\User\User;

/**
 * Description of Memory
 *
 * @author manuel
 */
class Memory extends User
{

    public function auth(UserInterface $user)
    {
        return (string) $this->password === (string) $user->getPassword() && parent::auth($user);
    }

}