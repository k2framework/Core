<?php

namespace KumbiaPHP\Di\Definition;

use KumbiaPHP\Di\Definition\DefinitionInterface;
use KumbiaPHP\Di\Definition\Service;

/**
 * Description of DefinitionManager
 *
 * @author manuel
 */
class DefinitionManager
{

    protected $services;
    protected $parameters;

    public function __construct()
    {
        $this->services = array();
        $this->parameters = array();
    }

    public function hasService($id)
    {
        return isset($this->services[$id]);
    }

    /**
     *
     * @param type $id
     * @return Service 
     */
    public function getService($id)
    {
        return $this->hasService($id) ? $this->services[$id] : NULL;
    }

    public function hasParam($id)
    {
        return isset($this->parameters[$id]);
    }

    public function getParam($id)
    {
        return $this->hasParam($id) ? $this->parameters[$id] : NULL;
    }

    public function addService(DefinitionInterface $definition)
    {
        if (!$this->hasService($definition->getId())) {
            $this->services[$definition->getId()] = $definition;
        }
        return $this;
    }
    public function addParam(DefinitionInterface $param)
    {
        if (!$this->hasParam($param->getId())) {
            $this->parameters[$param->getId()] = $param;
        }
        return $this;
    }
    
    public function getSerivces()
    {
        return $this->services;
    }
    
    public function getParams()
    {
        return $this->parameters;
    }

}