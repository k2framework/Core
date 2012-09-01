<?php

namespace KumbiaPHP\Di\Container;

/**
 * Contenedor de de las instancias de los servicios
 *
 * @author manuel
 */
class Services
{

    /**
     * Instancia de servicios
     * @var array 
     */
    protected $services;

    /**
     * Constructor de la clase
     * @param array $services 
     */
    function __construct(array $services = array())
    {
        $this->services = $services;
    }

    /**
     * Agrega una instancia si no existe
     * @param string $id
     * @param object $service 
     */
    public function add($id, $service)
    {
        if (!$this->has($id)) {
            $this->services[$id] = $service;
        }
    }

    /**
     * Verifica la existencia de una instancia
     * @param string $id
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->services[$id]);
    }

    /**
     * Devuelve la instancia del servicio si existe
     * @param string $id
     * @return object|NULL 
     */
    public function get($id)
    {
        return $this->has($id) ? $this->services[$id] : NULL;
    }

    /**
     * Elimina un objeto del arreglo de servicios
     * @param string $id 
     */
    public function remove($id)
    {
        if (!$this->has($id)) {
            unset($this->services[$id]);
        }
    }

    /**
     * Reemplaza la instancia de un servicios por otro si existe, si no, lo agrega
     * @param string $id
     * @param object $service 
     */
    public function replace($id, $service)
    {
        $this->services[$id] = $service;
    }

    /**
     * Limpia el arreglo que contiene los servicios. 
     */
    public function clear()
    {
        $this->services = array();
    }

}