<?php

namespace K2\Kernel;

use K2\Di\Container\Container;
use K2\EventDispatcher\EventDispatcherInterface;

class Module
{

    /**
     *
     * @var Container
     */
    protected $container;

    /**
     *
     * @var EventDispatcherInterface 
     */
    protected $dispatcher;

    /**
     *
     * @var string 
     */
    protected $path;
    protected $name;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function init()
    {
        
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        if (!$this->name) {
            $r = new \ReflectionObject($this);
            $this->name = str_replace('\\', '/', $r->getNamespaceName());
        }
        return $this->name;
    }

    public function getPath()
    {
        if (!$this->path) {
            $r = new \ReflectionObject($this);
            $this->path = dirname($r->getFileName()) . '/';
        }
        return $this->path;
    }

}
