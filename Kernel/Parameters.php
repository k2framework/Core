<?php

namespace KumbiaPHP\Kernel;

/**
 * Description of Parameters
 *
 * @author manuel
 */
class Parameters implements \Serializable
{

    protected $params;

    function __construct(array $params = array())
    {
        $this->params = $params;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->params);
    }

    public function get($key, $default = NULL)
    {
        return $this->has($key) ? $this->params[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function all()
    {
        return $this->params;
    }

    public function count()
    {
        return count($this->params);
    }

    public function delete($key)
    {
        if ($this->has($key)) {
            unset($this->params[$key]);
        }
    }

    public function clear()
    {
        $this->params = array();
    }

    public function serialize()
    {
        return serialize($this->params);
    }

    public function unserialize($serialized)
    {
        $this->params = unserialize($serialized);
    }

}