<?php

namespace KumbiaPHP\Di;

use KumbiaPHP\Di\Container\Container;

/**
 * 
 * @author manuel
 */
interface DependencyInjectionInterface
{

    public function newInstance($id, $className);

    public function setContainer(Container $container);
}
