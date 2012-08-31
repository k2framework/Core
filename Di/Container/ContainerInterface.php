<?php

namespace KumbiaPHP\Di\Container;

/**
 *
 * @author manuel
 */
interface ContainerInterface
{

    public function get($id);

    public function has($id);

    public function getParameter($id);

    public function hasParameter($id);
}
