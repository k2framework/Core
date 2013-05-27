<?php

namespace K2\Flash;

class FlashCollection implements \Iterator, \Serializable
{

    protected $flashes;
    protected $toDelete;

    public function __construct(array $flashes = array())
    {
        $this->toDelete = array();
        $this->flashes = $flashes;
    }

    /**
     * Agrega un mensaje flash
     * 
     * @param string $type tipo del mensaje ( success, info , error, advertencia )
     * @param string|array $message  el mensaje a guardar.
     */
    public function add($type, $message)
    {
        $type = trim($type);
        if (!isset($this->flashes[$type])) {
            $this->flashes[$type] = array();
            ++$this->types;
        }
        $this->flashes[$type] = array_merge($this->flashes[$type], (array) $message);
    }

    /**
     * Verifica la existencia de un mensaje en la clase, se debe pasar su tipo
     * @param string $type
     * @return boolean 
     */
    public function has($type)
    {
        $type = trim($type);
        return isset($this->flashes[$type]);
    }

    /**
     * Devuelve los mensajes que han sido previamente guardados, si existen.
     * 
     * antes de devolverlos, son borrados de la sesión.
     * 
     * @param string $type
     * @return array|NULL 
     */
    public function get($type)
    {
        $type = trim($type);
        if ($this->has($type)) {
            $messages = $this->flashes[$type];
            unset($this->flashes[$type]);
            return $messages;
        } else {
            return NULL;
        }
    }

    /**
     * Devuelve todos los mensajes guardados previamente y los borra
     * de la session.
     * 
     * @return array arreglo donde los indices son el tipo de mensaje y el valor
     * es el contenido del mensaje. 
     */
    public function all()
    {
        if (count($this->flashes)) {
            $this->toDelete = $this->flashes;
            $this->flashes = array();
        } else {
            $this->toDelete = array();
        }
        return $this->toDelete;
    }

    public function current()
    {
        return current($this->toDelete);
    }

    public function key()
    {
        return key($this->toDelete);
    }

    public function next()
    {
        next($this->toDelete);
    }

    public function rewind()
    {
        $this->all();
        reset($this->toDelete);
    }

    public function valid()
    {
        static $x = 0;
        if (++$x > 20) {
            return false;
        }
        return false !== current($this->toDelete);
    }

    public function serialize()
    {
        return serialize($this->flashes);
    }

    public function unserialize($serialized)
    {
        $this->flashes = unserialize($serialized);
    }

}
