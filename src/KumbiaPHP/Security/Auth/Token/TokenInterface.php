<?php

namespace KumbiaPHP\Security\Auth\Token;

use \Serializable;
use KumbiaPHP\Security\Auth\User\UserInterface;

/**
 *
 * @author manuel
 */
interface TokenInterface extends Serializable
{

    public function __construct(UserInterface $user);

    /**
     * @return UserInterface 
     */
    public function getUser();

    public function getUsername();

    public function getRoles();

    /**
     * 
     */
    public function setUser(UserInterface $user);

    public function setAutenticated($autenticate);

    public function logout();

    public function isValid();
}
