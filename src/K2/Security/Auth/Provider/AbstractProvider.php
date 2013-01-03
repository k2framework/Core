<?php

namespace K2\Security\Auth\Provider;

use K2\Di\Container\ContainerInterface;
use K2\Security\Auth\Provider\UserProviderInterface;

/**
 * Description of Memory
 *
 * @author manuel
 */
abstract class AbstractProvider implements UserProviderInterface
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

}