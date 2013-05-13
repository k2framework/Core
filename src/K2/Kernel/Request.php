<?php

namespace K2\Kernel;

use K2\Kernel\AppContext;
use K2\Kernel\Session\SessionInterface;

/**
 * Esta clase representa una petición HTTP.
 *
 * @author manuel
 */
class Request
{

    /**
     * Contiene el contexto de ejecución de la aplicación
     * @var AppContext
     */
    protected $app;

    /**
     * Contenido del Request Body de la petición
     * @var string 
     */
    protected $content = false;

    /**
     * Locale actual usado en la App
     * @var string 
     */
    protected $locale;
    protected $uri;

    /**
     * Constructor de la clase. 
     */
    public function __construct($uri)
    {
        //este fix es para permitir tener en el request los valores para peticiones
        //PUT y DELETE, ya que php no ofrece una forma facil de obtenerlos
        //actualmente.
        if (0 === strpos($this->server('CONTENT_TYPE'), 'application/x-www-form-urlencoded') && in_array($this->getMethod(), array('PUT', 'DELETE'))
        ) {
            parse_str($this->getContent(), $_REQUEST);
        } elseif (0 === strpos($this->server('CONTENT_TYPE'), 'application/json')) {
            //si los datos de la petición se envian en formato JSON
            //los convertimos en una arreglo.
            $_REQUEST = json_decode($this->getContent(), true);
        }

        $this->uri = $uri;
    }

    /**
     * Devuelve un valor del arreglo $_GET
     * @param string $key indice del arreglo
     * @param mixed $default valor por defecto si no existe el indice
     * @return mixed valor retornado
     */
    public function get($key, $default = null)
    {
        return filter_has_var(INPUT_GET, $key) ? filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING) : $default;
    }

    /**
     * Devuelve un valor del arreglo $_POST
     * @param string $key indice del arreglo
     * @param mixed $default valor por defecto si no existe el indice
     * @return mixed valor retornado
     */
    public function post($key, $default = null)
    {
        return filter_has_var(INPUT_POST, $key) ? $_POST[$key] : $default;
    }

    /**
     * Devuelve un valor del arreglo $_REQUEST
     * @param string $key indice del arreglo
     * @param mixed $default valor por defecto si no existe el indice
     * @return mixed valor retornado
     */
    public function request($key, $default = null)
    {
        return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
    }

    /**
     * Devuelve la instancia del objeto que tiene el contexto de la aplicación
     * @return AppContext
     */
    public function getAppContext()
    {
        return $this->app;
    }

    /**
     * Establece la instancia del objeto que tiene el contexto de la aplicación
     * @param SessionInterface $session 
     */
    public function setAppContext(AppContext $app)
    {
        $this->app = $app;
    }

    /**
     * Devuelve el método de la petición GET, POST, DELETE, PUT, ...
     * @return string 
     */
    public function getMethod()
    {
        return $this->server('REQUEST_METHOD', 'GET');
    }

    /**
     * Devuelve la IP del cliente
     * @return string 
     */
    public function getClientIp()
    {
        return $this->server('REMOTE_ADDR');
    }

    /**
     * Devuelve true si la petición es Ajax
     * @return boolean 
     */
    public function isAjax()
    {
        return 'XMLHttpRequest' === $this->server('HTTP_X_REQUESTED_WITH');
    }

    /**
     * Devuelve true si el método de la petición es el pasado por parametro
     * @param string $method método que se quiere verificar
     * @return boolean 
     */
    public function isMethod($method)
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }

    /**
     * Devuelve la url de la petición actual
     * @return type 
     */
    public function getRequestUrl()
    {
        return $this->uri;
    }

    /**
     * Devuelve el Cuerpo de la petición
     * @return string 
     */
    public function getContent()
    {
        if (false === $this->content) {
            $this->content = file_get_contents('php://input');
        }
        return $this->content;
    }

    public function __clone()
    {
        $this->__construct($this->uri);
    }

    /**
     * Obtiene el Locale del Petición.
     * @return type 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Establece el Locale de la Petición.
     * @param string $locale 
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function server($key, $default = null)
    {
        return array_key_exists($key, $_SERVER) ? $_SERVER[$key] : $default;
    }

}