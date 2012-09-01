<?php

namespace KumbiaPHP\Kernel\Controller;

use KumbiaPHP\Di\Container\ContainerInterface;
use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Router\Router;
use KumbiaPHP\Kernel\Response;

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
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
     * @param string $view
     * @param string $template 
     */
    protected function setView($view, $template = FALSE)
    {
        $this->view = $view;
        if ($template !== FALSE) {
            $this->setTemplate($template);
        }
    }

    /**
     * Establece el template a usar
     * @param string $template 
     */
    protected function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * devuelve la vista a mostrar
     * @return string 
     */
    protected function getView()
    {
        return $this->view;
    }

    /**
     * devuelve el template a mostarr
     * @return string 
     */
    protected function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sirve para enviar al servicio de template "view" una respuesta
     * especifica con los parametros pasados a este metodo.
     * @param Response $response
     * @param array $params
     * @return type 
     */
    protected function render(Response $response, array $params = array())
    {
        return $this->get('view')->render($this->template, $this->view, $params, $response);
    }

}