<?php

namespace KumbiaPHP\Security\Auth\Token;

use KumbiaPHP\Security\Auth\User\UserInterface;
use KumbiaPHP\Security\Auth\Token\TokenInterface;

/**
 * Description of Token
 *
 * @author manuel
 */
abstract class AbstractToken implements TokenInterface
{

    /**
     *  
     * @var UserInterface
     */
    protected $user;
    protected $valid;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getUsername()
    {
        return $this->user->getUsername();
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function logout()
    {
        
    }

    public function serialize()
    {
        return serialize(array($this->user, $this->valid));
    }

    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    public function unserialize($serialized)
    {
        list($this->user, $this->valid) = unserialize($serialized);
    }

    public function setAutenticated($autenticate)
    {
        $this->valid = $autenticate;
    }

    public function getRoles()
    {
        return $this->user->getRoles();
    }

}