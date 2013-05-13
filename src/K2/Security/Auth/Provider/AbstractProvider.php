<?php

namespace K2\Security\Auth\Provider;

use K2\Di\Container\Container;
use K2\Security\Auth\Provider\UserProviderInterface;

/**
 * Description of Memory
 *
 * @author manuel
 */
abstract class AbstractProvider implements UserProviderInterface
{

    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

}