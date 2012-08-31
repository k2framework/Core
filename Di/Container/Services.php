<?php

namespace KumbiaPHP\Di\Container;

/**
 * Description of Services
 *
 * @author manuel
 */
class Services
{

    protected $services;

    function __construct(array $services = array())
    {
        $this->services = $services;
    }

    public function add($id, $service)
    {
        if (!$this->has($id)) {
            $this->services[$id] = $service;
        }
    }

    public function has($id)
    {
        return isset($this->services[$id]);
    }

    public function get($id)
    {
        return $this->has($id) ? $this->services[$id] : NULL;
    }

    public function remove($id)
    {
        if (!$this->has($id)) {
            unset($this->services[$id]);
        }
    }

    public function replace($id, $service)
    {
        $this->services[$id] = $service;
    }

    public function clear()
    {
        $this->services = array();
    }

}