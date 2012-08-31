<?php

namespace KumbiaPHP\Di\Definition;

use KumbiaPHP\Di\Definition\DefinitionInterface;

/**
 * Description of Service
 *
 * @author manuel
 */
class Service implements DefinitionInterface
{

    protected $id;
    protected $config;

    public function __construct($id, $config)
    {
        $this->id = $id;
        $this->config = $config;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

}