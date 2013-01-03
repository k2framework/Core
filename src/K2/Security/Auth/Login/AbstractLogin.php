<?php

namespace K2\Security\Auth\Login;

use K2\Kernel\Response;
use K2\Di\Container\ContainerInterface;

/**
 * Description of LoginInterface
 *
 * @author manuel
 */
abstract class AbstractLogin
{

    /**
     *
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Response 
     */
    abstract public function showLogin();
}