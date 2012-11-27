<?php

namespace KumbiaPHP\Kernel\Router;

use KumbiaPHP\Kernel\Kernel;
use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Config\Reader;
use KumbiaPHP\Kernel\RedirectResponse;
use KumbiaPHP\Kernel\Event\RequestEvent;
use KumbiaPHP\Kernel\Router\RouterInterface;

/**
 * Servicio Router del framework
 *
 * @author manuel
 */
class Router implements RouterInterface
{

    /**
     * Numero de veces que se llama al método forward en una misma petición.
     * @var int 
     */
    private $forwards = 0;

    /**
     * Redirije la petición a otro modulo/controlador/accion de la aplicación.
     * @param string $url
     * @return \KumbiaPHP\Kernel\RedirectResponse 
     */
    public function redirect($url = NULL, $status = 302)
    {
        return new RedirectResponse(Kernel::get('app')->createUrl($url), $status);
    }

    /**
     * Redirije la petición a otra acción del mismo controlador.
     * @param type $action
     * @return \KumbiaPHP\Kernel\RedirectResponse 
     */
    public function toAction($action = NULL, $status = 302)
    {
        $url = Kernel::get('app')->getControllerUrl($action);
        return new RedirectResponse($url, $status);
    }

    /**
     * Redirije la petición a otro modulo/controlador/accion de la aplicación internamente,
     * es decir, la url del navegador no va a cambiar para el usuario.
     * @param type $url
     * @return type
     * @throws \LogicException 
     */
    public function forward($url)
    {
        if ($this->forwards++ > 10) {
            throw new \LogicException("Se ha detectado un ciclo de redirección Infinito...!!!");
        }
        //clono el request y le asigno la nueva url.
        $request = clone Kernel::get('request');

        $request->query->set('_url', '/' . ltrim(Kernel::get('app')->createUrl($url, false), '/'));

        //retorno la respuesta del kernel.
        return Kernel::get('kernel')->execute($request, Kernel::SUB_REQUEST);
    }

    public function rewrite(RequestEvent $event)
    {
        Reader::read('routes');

        $url = $event->getRequest()->getRequestUrl();
        $routes = Reader::get('routes');

        if (isset($routes[$url])) {
            //si existe la ruta exacta usamos esa
            $url = $routes[$url];
        } else {
            // Si existe una ruta con el comodin * crea la nueva ruta
            foreach ($routes as $key => $val) {
                if ($key == '/*') {
                    return rtrim($val, '*') . $url;
                }

                if (strripos($key, '*', -1)) {
                    $key = rtrim($key, '*');
                    if (strncmp($url, $key, strlen($key)) == 0)
                        return str_replace($key, rtrim($val, '*'), $url);
                }
            }
        }

        $event->getRequest()->query->set('_url', $url);
    }

}