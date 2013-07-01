<?php

namespace K2\Di\Container;

use K2\Di\Exception\DiException;
use K2\Di\Exception\IndexNotDefinedException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Description of Container
 *
 * @author manuel
 */
class Container implements \ArrayAccess
{

    /**
     * 
     * @var array
     */
    protected $services;

    /**
     *
     * @var array 
     */
    protected $definitions;

    /**
     *
     * @var PropertyAccessor
     */
    protected $pAccesor;

    public function __construct()
    {
        $this->services = array();
        $this->definitions = array(
            'parameters' => array(),
            'services' => array()
        );

        //agregamos al container como servicio.
        $this->setInstance('container', $this);
        $this->pAccesor = new PropertyAccessor();
        $this->setInstance('property_accesor', $this->pAccesor);
    }

    public function get($id)
    {

        //si no existe lanzamos la excepcion
        if (!$this->has($id)) {
            throw new IndexNotDefinedException(sprintf('No existe el servicio "%s"', $id));
        }
        //si existe el servicio y está creado lo devolvemos
        if ($this->hasInstance($id)) {
            return $this->services[$id];
        }
        //si existe pero no se ha creado, creamos la instancia
        $instance = $this->getInstance($id);

        if (!is_object($instance)) {
            throw new DiException("La función que crea el servicio $id bebe retornar un objeto");
        }

        return $instance;
    }

    public function has($id)
    {
        return isset($this->definitions['services'][$id]);
    }

    public function hasInstance($id)
    {
        return isset($this->services[$id]);
    }

    /**
     * Establece una instancia de un objeto en el indice especificado
     * @param string $id indice
     * @param object $object objeto a almacenar
     */
    public function setInstance($id, $object)
    {
        $this->services[$id] = $object;
        //y lo agregamos a las definiciones. (solo será a gregado si no existe)
        if (!isset($this->definitions['services'][$id])) {

            $this->definitions['services'][$id] = true;
        }
    }

    public function removeInstance($id)
    {
        if ($this->hasInstance($id)) {
            unset($this->services[$id]);
        }
    }

    public function getParameter($id)
    {
        try {
            $id = '[' . str_replace('.', '][', $id) . ']';
            return $this->pAccesor->getValue($this->definitions['parameters'], $id);
        } catch (\RuntimeException $e) {
            return null;
        }
    }

    public function hasParameter($id)
    {
        try {
            $id = '[' . str_replace('.', '][', $id) . ']';
            $val = $this->pAccesor->getValue($this->definitions['parameters'], $id);
            return $val != null;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function setParameter($id, $value)
    {
        try {
            if (is_array($value) and is_array($original = $this->getParameter($id))) {
                $value = $this->merge($original, $value);
            }
            $id = '[' . str_replace('.', '][', $id) . ']';
            return $this->pAccesor->setValue($this->definitions['parameters'], $id, $value);
        } catch (\RuntimeException $e) {
            
        }

        return $this;
    }

    /**
     * Crea ó Actualiza la configuración para la creación de un servicio.
     * 
     * @example $container->set("session", function($c){
     *      return new K2\Kernel\Session\Session($c['request']);
     * });
     * 
     * @param string $id identificador del servicio
     * @param \Closure funcion que crea el servicio
     * @param boolean $singleton Indica si se va a mentener una sola instancia de la clase
     * ó se creará una nueva cada vez que el servicio sea solicitado
     */
    public function set($id, \Closure $function, $singleton = true)
    {
        $this->definitions['services'][$id] = array($function, $singleton);
        return $this;
    }

    /**
     * Crea ó Actualiza la configuración para la creación de varios servicios.
     * 
     * @example $container->setFromArray(array(
     *                  "session" => function($c){
     *                      return new K2\Kernel\Session\Session($c['request']);
     *                  },
     *                  "flash" => function($c){
     *                      return new K2\Flash\Flash($c['session']);
     *                  },
     *               )
     * );
     * 
     * @param array $services Arreglo con las definciones de los servicios
     */
    public function setFromArray(array $services)
    {
        $this->definitions['services'] = $services + $this->definitions['services'];
    }

    /**
     * Verifica la existencia de un serivicio ó un parametro en el contenedor.
     * @param string $offset
     * @return boolean 
     */
    public function offsetExists($offset)
    {
        return $this->has($offset) || $this->hasParameter($offset);
    }

    /**
     * Devuelve la instancia de una clase si está definida, sino devuelve un parametro,
     * si tampoco existe, devuelve null.
     * @param string $offset
     * @return mixed 
     */
    public function offsetGet($offset)
    {
        if ($this->has($offset)) {
            return $this->get($offset);
        } elseif ($this->hasParameter($offset)) {
            return $this->getParameter($offset);
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value)
    {
        if ($value instanceof \Closure) {
            $this->set($offset, $value);
        } else {
            $this->setParameter($offset, $value);
        }
    }

    public function offsetUnset($offset)
    {
        //nada por ahora
    }

    public function getTaggedServicesConfig($tagName)
    {
        $services = array();
        
        foreach($this->definitions['services'] as $id => $config){
            if(is_array($config) and isset($config['tags'][$tagName])){
                $services[$id] = $config['tags'][$tagName];
            }
        }

        return $services;
    }

    protected function getInstance($id)
    {
        $data = $this->definitions['services'][$id];

        if ($data instanceof \Closure) {
            return $this->services[$id] = $data($this);
        }

        if (is_array($data)) {

            if (!isset($data['callback']) or !$data['callback'] instanceof \Closure) {
                throw new DiException("No se reconoce el valor para la definición del servicio $id");
            }

            $instance = $data['callback']($this);

            if (!array_key_exists('singleton', $data) 
                or (isset($data['singleton']) and true === $data['singleton'])) {
                $this->services[$id] = $instance;
            }

            return $instance;
        }

        throw new DiException("No se reconoce el valor para la definición del servicio $id");
    }

    protected function merge(array &$original, array &$data)
    {
        $merged = $original;

        foreach ($data as $key => &$value) {
            if (is_array($value) && isset($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = $this->merge($merged [$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

}