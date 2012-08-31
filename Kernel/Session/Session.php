<?php

namespace KumbiaPHP\Kernel\Session;

use KumbiaPHP\Kernel\Session\SessionInterface;

/**
 * Description of Session
 *
 * @author manuel
 */
class Session implements SessionInterface
{

    protected $namespace;

    public function __construct($namespace = 'default')
    {
        $this->namespace = $namespace;
        $this->start();
    }

    public function start()
    {
        session_start();
    }

    public function destroy()
    {
        session_unset();
        session_destroy();
    }

    public function get($key)
    {
        return $this->has($key) ? $_SESSION[$this->namespace][$key] : NULL;
    }

    public function has($key)
    {
        return isset($_SESSION[$this->namespace]) && array_key_exists($key, $_SESSION[$this->namespace]);
    }

    public function set($key, $value)
    {
        $_SESSION[$this->namespace][$key] = $value;
    }

    public function delete($key)
    {
        if ($this->has($key)) {
            unset($_SESSION[$this->namespace][$key]);
        }
    }

}