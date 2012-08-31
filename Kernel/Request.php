<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Kernel\Session\SessionInterface;
use KumbiaPHP\Kernel\Parameters;

/**
 * Esta clase representa una petici�n HTTP.
 *
 * @author manuel
 */
class Request
{

    /**
     * Contiene la Informaci�n de la variable $_SERVER
     * @var Parameters
     */
    public $server;

    /**
     * Contiene la Informaci�n de la variable $_REQUEST
     * @var Parameters
     */
    public $request;

    /**
     * Contiene la Informaci�n de la variable $_GET
     * @var Parameters 
     */
    public $query;

    /**
     * Contiene la informaci�n de la variable $_COOKIE
     * @var Parameters 
     */
    public $cookies;

    /**
     * Contiene la informaci�n de la variable $_FILES
     * @var Parameters 
     */
    public $files;

    /**
     * Contiene la Informaci�n de la Sesi�n
     * @var SessionInterface 
     */
    protected $session;
    /**
     *
     * @var string 
     */
    private $baseUrl;

    public function __construct()
    {
        $this->server = new Parameters($_SERVER);
        $this->request = new Parameters($_POST);
        $this->query = new Parameters($_GET);
        $this->cookies = new Parameters($_COOKIE);
        $this->files = new Parameters($_FILES);
    }

    public function get($key, $default = NULL)
    {
        //busca en request, si no existe busca en query sino existe busca en 
        //cookies, si no devuelve $default.
        return $this->request->get($key, $this->query->get($key, $this->cookies->get($key, $default)));
    }

    /**
     *
     * @return SessionInterface 
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @Service(session)
     * @param SessionInterface $session 
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getMethod()
    {
        return $this->server->get('REQUEST_METHOD', 'GET');
    }

    public function getClientIp()
    {
        return $this->server->get('REMOTE_ADDR');
    }

    public function isAjax()
    {
        return $this->server->get('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    public function isMethod($method)
    {
        return $this->getMethod() === $method;
    }

    public function getBaseUrl()
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->createBaseUrl();
        }
        return $this->baseUrl;
    }

    private function createBaseUrl()
    {
        return dirname($this->server->get('SCRIPT_NAME')) . '/';
    }

    public function getRequestUrl()
    {
        return $this->query->get('_url', '/');
    }

}