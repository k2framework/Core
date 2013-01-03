<?php

namespace K2\Security\Auth\User;

use K2\Security\Auth\User\UserInterface;

/**
 * Description of User
 *
 * @author manuel
 */
class User implements UserInterface
{

    protected $username;
    protected $password;
    protected $roles;

    function __construct(array $data = array())
    {
        isset($data['username']) && $this->username = $data['username'];
        isset($data['password']) && $this->password = $data['password'];
        isset($data['roles']) && $this->roles = $data['roles'];
    }

    public function auth(UserInterface $user)
    {
        return TRUE;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getUsername()
    {
        return $this->username;
    }
}