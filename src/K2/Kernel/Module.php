<?php

namespace K2\Kernel;

use K2\Di\Container\Container;
use K2\EventDispatcher\EventDispatcher;

class Module
{

    /**
     *
     * @var Container
     */
    protected $container;

    /**
     *
     * @var EventDispatcher 
     */
    protected $dispatcher;

    /**
     *
     * @var string 
     */
    protected $path;
    protected $name;
    protected $namespace;

    /**
     *
     * @var Module
     */
    private $children;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function setEventDispatcher(EventDispatcher $dispatcher)
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
            $this->name = str_replace('\\', '/', $this->getNamespace());
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

    public function getNamespace()
    {
        if (!$this->namespace) {
            $r = new \ReflectionObject($this);
            $this->namespace = $r->getNamespaceName();
        }
        return $this->namespace;
    }

    public function extend()
    {
        return null;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return null !== $this->children;
    }

    public function setChildren(Module $children)
    {
        $this->children = $children;
    }

}
