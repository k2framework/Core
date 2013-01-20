<?php

namespace K2\Kernel\Controller;

use K2\Di\Container\ContainerInterface;
use K2\Kernel\Request;
use K2\Kernel\Router\Router;
use K2\Kernel\Response;

/**
 * Controlador padre de todos los controllers de la aplicación
 *
 * @author manuel
 */
class Controller
{

    /**
     *
     * @var ContainerInterface; 
     */
    protected $container;

    /**
     * Vista a llamar por el servicio de template @view
     * @var string 
     */
    protected $view;

    /**
     * Template a llamar por el servicio de template @template
     * @var string 
     */
    protected $template = 'default';

    /**
     * response a usar en la vista, por ejemplo si response es xml la vista será
     * 
     * nombrevista.xml.phtml, si es json la vista es por ejemplo index.json.phtml
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
    protected $limitParams = TRUE;

    /**
     * parametros de la url
     * @var array 
     */
    protected $parameters;

    /**
     * Constructor de la clase
     * @param ContainerInterface $container
     */
    public final function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function renderNotFound($message)
    {
        throw new \K2\Kernel\Exception\NotFoundException($message);
    }

    /**
     *
     * @return object
     */
    protected function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Devuelve el objeto resquest de la petición
     * @return Request 
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * Devuelve el servicio router
     * @return Router 
     */
    protected function getRouter()
    {
        return $this->container->get('router');
    }

    /**
     * Establece la vista a usar
     * @final
     * @param string $view
     * @param string $template 
     */
    final public function setView($view, $template = false)
    {
        $this->view = $view;
        if ($template !== false) {
            $this->setTemplate($template);
        }
    }

    /**
     * Establece el response para la vista
     * @final
     * @param string $response
     * @param string $template 
     */
    final public function setResponse($response, $template = false)
    {
        $this->response = $response;
        if ($template !== false) {
            $this->setTemplate($template);
        }
    }

    /**
     * Establece el template a usar
     * @final
     * @param string $template 
     */
    final public function setTemplate($template)
    {
        $this->template = $template;
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
     * devuelve el template a mostar
     * @final
     * @return string 
     */
    final public function getTemplate()
    {
        return $this->template;
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
    protected function render(array $params = array(), $time = null)
    {
        return $this->get('view')->render(array(
                    'template' => $this->getTemplate(),
                    'view' => $this->getView(),
                    'response' => $this->getResponse(),
                    'params' => $params,
                    'time' => $time,
                ));
    }

}