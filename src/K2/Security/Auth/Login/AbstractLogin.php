<?php

namespace K2\Security\Auth\Login;

use K2\Kernel\Response;
use K2\Di\Container\Container;

/**
 * Description of LoginInterface
 *
 * @author manuel
 */
abstract class AbstractLogin
{

    /**
     *
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Response 
     */
    abstract public function showLogin();
}