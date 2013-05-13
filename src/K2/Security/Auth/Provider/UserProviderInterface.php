<?php

namespace K2\Security\Auth\Provider;

use K2\Security\Auth\Token\TokenInterface;
use K2\Security\Auth\User\UserInterface;
use K2\Di\Container\Container;

/**
 *
 * @author manuel
 */
interface UserProviderInterface
{

    public function __construct(Container $container);
    /**
     *  @return UserInterface
     */
    public function loadUser(TokenInterface $token);
    public function getToken(array $config = array(), array $data = null);
}
