<?php

namespace KumbiaPHP\Kernel\Controller;

use KumbiaPHP\Di\Container\ContainerInterface;
use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Router\Router;
use KumbiaPHP\Kernel\Response;

/**
 * Description of Controller
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
    protected $view;
    protected $template = 'default';
    protected $limitParams = TRUE;
    protected $parameters;

    /**
     * @Service(container,$container)
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
     *
     * @return Request 
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }
    
    /**
     *
     * @return Router 
     */
    protected function getRouter()
    {
        return $this->container->get('router');
    }

    protected function setView($view, $template = FALSE)
    {
        $this->view = $view;
        if ($template !== FALSE) {
            $this->setTemplate($template);
        }
    }

    protected function setTemplate($template)
    {
        $this->template = $template;
    }

    protected function getView()
    {
        return $this->view;
    }

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