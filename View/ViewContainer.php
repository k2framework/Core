<?php

namespace KumbiaPHP\View;

use \ArrayAccess;
use KumbiaPHP\Di\Container\Container;
use KumbiaPHP\Di\Definition\Service;

/**
 * Clase contenedora de los helpers para las vistas
 *
 * @author maguirre
 */
class ViewContainer implements ArrayAccess
{

    public $content;

    /**
     * 
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $container->getDefinitionManager()
                ->addService(new Service('html', array(
                            'class' => 'KumbiaPHP\\View\\Helper\\Html',
                            'construct' => '@app.context'
                        )))
                ->addService(new Service('tag', array(
                            'class' => 'KumbiaPHP\\View\\Helper\\Tag',
                            'construct' => '@app.context'
                        )))
                ->addService(new Service('form', array(
                            'class' => 'KumbiaPHP\\View\\Helper\\Form',
                        )))
        ;
        $this->container = $container;
    }

    public function offsetExists($offset)
    {
        return $this->container->has($offset);
    }

    public function offsetGet($offset)
    {
        try {
            return $this->container->get($offset);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('No existe el helper \"%s\"', $offset));
        }
    }

    public function offsetSet($offset, $value)
    {
        //nada por ahora
    }

    public function offsetUnset($offset)
    {
        //nada por ahora
    }

    public function __toString()
    {
        return $this->content;
    }

}
