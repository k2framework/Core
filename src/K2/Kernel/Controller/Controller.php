<?php

namespace K2\Kernel\Controller;

use K2\Kernel\App;
use K2\Kernel\Response;
use K2\Kernel\Router\RouterInterface;

/**
 * Controlador padre de todos los controllers de la aplicación
 *
 * @author manuel
 */
class Controller
{

    /**
     * Vista a llamar por el servicio de template @view
     * @var string 
     */
    protected $view;

    /**
     * response a usar en la vista, por ejemplo si response es xml la vista será
     * 
     * nombrevista.xml.twig, si es json la vista es por ejemplo index.json.twig
     * 
     * @var string 
     */
    protected $response;

    /**
     * Tiempo de cacheado ( debe ser una fecha relativa ).
     * 
     * @var string 
     */
    protected $cache = null;

    /**
     * indica si se deben limitar el numero de parametros en las acciones ó no.
     * @var boolean 
     */
    protected $limitParams = true;

    /**
     * parametros de la url
     * @var array 
     */
    protected $parameters;

    protected function renderNotFound($message)
    {
        throw new \K2\Kernel\Exception\NotFoundException($message);
    }

    /**
     * 
     * @return \K2\Kernel\Request;
     */
    protected function getRequest()
    {
        return App::getRequest();
    }

    /**
     * Devuelve el servicio router
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return App::get('router');
    }

    /**
     * Establece la vista a usar
     * @final
     * @param string $view 
     */
    final public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Establece el response para la vista
     * @final
     * @param string $response
     */
    final public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * devuelve la vista a mostrar
     * @final
     * @return string 
     */
    final public function getView()
    {
        return $this->view;
    }

    /**
     * devuelve el response a usar
     * @final
     * @return string 
     */
    final public function getResponse()
    {
        return $this->response;
    }

    /**
     * Especifica un tiempo de cache para la vista.
     * 
     * Debe ser una cadena que represente un formato de fecha relativa.
     * @final
     * 
     * @example $this->cache("+2 days");
     * @example $this->cache("+3 hours");
     * @example $this->cache("+10 sec");
     * 
     * @param string $time
     */
    final public function cache($time = false)
    {
        $this->cache = $time;
    }

    final public function getCache()
    {
        return $this->cache;
    }

    /**
     * Sirve para llamar al servicio de template "view" pasandole
     * unos parametros  y especificando el tiempo de cache.
     * 
     * @param array $params
     * @param type $time
     * @return Response 
     */
    protected function render($view, array $params = array(), $time = null)
    {
        return App::get('view')->render($view, array(
                    'response' => $this->getResponse(),
                    'params' => $params,
                    'time' => $time,
        ));
    }

}