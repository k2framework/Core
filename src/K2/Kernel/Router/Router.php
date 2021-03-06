<?php

namespace K2\Kernel\Router;

use K2\Kernel\App;
use K2\Kernel\Kernel;
use K2\Kernel\Config\Reader;
use K2\Kernel\RedirectResponse;
use K2\Kernel\Event\RequestEvent;
use K2\Kernel\Router\RouterInterface;
use K2\Kernel\Exception\NotFoundException;

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
     * @return \K2\Kernel\RedirectResponse 
     */
    public function redirect($url = NULL, $status = 302)
    {
        return new RedirectResponse($this->createUrl($url), $status);
    }

    /**
     * Redirije la petición a otra acción del mismo controlador.
     * @param type $action
     * @return \K2\Kernel\RedirectResponse 
     */
    public function toAction($action = NULL, $status = 302)
    {
        $context = App::getContext();

        $url = '';

        $context['module_url'] && $url .= $context['module_url'] . '/';
        $context['controller'] && $url .= $context['controller'] . '/';

        $url .= $action;

        return $this->redirect($url, $status);
    }

    /**
     * Redirije la petición a otro modulo/controlador/accion de la aplicación internamente,
     * es decir, la url del navegador no va a cambiar para el usuario.
     * @param type $url
     * @return \K2\Kernel\Response
     * @throws \LogicException 
     */
    public function forward($url)
    {
        if ($this->forwards++ > 10) {
            throw new \LogicException("Se ha detectado un ciclo de redirección Infinito...!!!");
        }

        $url = '/' . trim($this->createUrl($url, false), '/');

        //retorno la respuesta del kernel.
        return App::get('app.kernel')->execute(new \K2\Kernel\Request($url), Kernel::SUB_REQUEST);
    }

    public function rewrite(RequestEvent $event)
    {
        Reader::read('routes');

        $newUrl = $url = $event->getRequest()->getRequestUrl();
        $routes = Reader::get('routes.routes');

        if (isset($routes[$url])) {
            //si existe la ruta exacta usamos esa
            $newUrl = $routes[$url];
        } else {
            // Si existe una ruta con el comodin * crea la nueva ruta
            foreach ($routes as $key => $val) {
                if ($key == '/*') {
                    $newUrl = rtrim($val, '/*') . $url;
                    break;
                } elseif (strripos($key, '*', -1)) {
                    $key = rtrim($key, '/*');
                    if (strncmp($url, $key, strlen($key)) == 0) {
                        $newUrl = str_replace($key, rtrim($val, '/*'), $url);
                        break;
                    }
                }
            }
        }

        //si la url fué reescrita
        if ($newUrl !== $url) {
            //actualizamos la url en el Request y llamamos al parseUrl del AppContext
            $event->getRequest()->query->set('_url', '/' . ltrim($newUrl, '/'));
            App::get('app.kernel')->parseUrl();
        }
    }

    

    public function createUrl($url, $baseUrl = true)
    {
        if (0 === strpos($url, '@')) {
            $url = explode('/', trim(substr($url, 1), '/'), 2);
            if (!$route = array_search($url[0], App::routes())) {
                throw new NotFoundException("No Existe el módulo {$url[0]}, no se pudo crear la url");
            }
            if (count($url) > 1) {
                $url = trim($route, '/') . '/' . $url[1];
            } else {
                $url = trim($route, '/');
            }
        }
        $url = trim($url, '/');
        //si se usa locale, lo añadimos a la url.
        App::getRequest()->getLocale() && $url = App::getRequest()->getLocale() . '/' . $url;
        return $baseUrl ? PUBLIC_PATH . $url : $url;
    }

}