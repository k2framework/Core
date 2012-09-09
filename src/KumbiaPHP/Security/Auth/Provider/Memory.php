<?php

namespace KumbiaPHP\Security\Auth\Provider;

use KumbiaPHP\Security\Config\Reader;
use KumbiaPHP\Security\Auth\User\User;
use KumbiaPHP\Security\Auth\User\UserInterface;
use KumbiaPHP\Security\Auth\Token\TokenInterface;
use KumbiaPHP\Security\Auth\Provider\UserProviderInterface;

/**
 * Description of Memory
 *
 * @author manuel
 */
class Memory implements UserProviderInterface
{

    //put your code here
    public function loadUser(TokenInterface $token)
    {
        $users = (array) Reader::get('users');

        if (!isset($users[$token->getUsername()])) {
            die('no existe');
        }

        $data['username'] = $token->getUsername();
        $data['password'] = key($users[$token->getUsername()]);
        $data['roles'] = explode(',', $users[$token->getUsername()][$data['password']]);

        return new User($data);
    }

}