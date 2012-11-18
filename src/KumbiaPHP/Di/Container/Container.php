<?php

namespace KumbiaPHP\Di\Container;

use KumbiaPHP\Di\Container\ContainerInterface;
use KumbiaPHP\Di\DependencyInjectionInterface as Di;
use KumbiaPHP\Di\Definition\Service;
use KumbiaPHP\Di\Exception\IndexNotDefinedException;

/**
 * Description of Container
 *
 * @author manuel
 */
class Container implements ContainerInterface
{

    /**
     * 
     * @var array
     */
    protected $services;

    /**
     *
     * @var Di 
     */
    protected $di;

    /**
     *
     * @var array 
     */
    protected $definitions;

    public function __construct(Di $di, array $definitions = array())
    {
        $this->services = array();
        $this->di = $di;
        $this->definitions = $definitions;

        $di->setContainer($this);

        //agregamos al container como servicio.
        $this->set('container', $this);
    }

    public function get($id)
    {

        if ($this->has($id)) {
            //si existe el servicio lo devolvemos
            return $this->services[$id];
        }
        //si no existe debemos crearlo
        //buscamos el servicio en el contenedor de servicios
        if (!isset($this->definitions['services'][$id])) {
            throw new IndexNotDefinedException(sprintf('No existe el servicio "%s"', $id));
        }

        $config = $this->definitions['services'][$id];

        //retorna la instancia recien creada
        return $this->di->newInstance($id, $config);
    }

    public function has($id)
    {
        return isset($this->services[$id]);
    }

    public function set($id, $object)
    {
        $this->services[$id] = $object;
        //y lo agregamos a las definiciones. (solo será a gregado si no existe)
        if (!isset($this->definitions['services'][$id])) {

            $this->definitions['services'][$id] = array(
                'class' => get_class($object)
            );
        }
    }

    public function getParameter($id)
    {
        if ($this->hasParameter($id)) {
            return $this->definitions['parameters'][$id];
        } else {
            return NULL;
        }
    }

    public function hasParameter($id)
    {
        return array_key_exists($id, $this->definitions['parameters']);
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function setParameter($id, $value)
    {
        $this->definitions['parameters'][$id] = $value;
    }

}