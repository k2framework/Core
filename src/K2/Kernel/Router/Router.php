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
        return new RedirectResponse(App::get('app.context')->createUrl($url), $status);
    }

    /**
     * Redirije la petición a otra acción del mismo controlador.
     * @param type $action
     * @return \K2\Kernel\RedirectResponse 
     */
    public function toAction($action = NULL, $status = 302)
    {
        $url = App::get('app.context')->getControllerUrl($action);
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
        $request = clone App::getRequest();

        $_GET['_url'] = '/' . ltrim(App::get('app.context')->createUrl($url, false), '/');

        //retorno la respuesta del kernel.
        return App::get('app.kernel')->execute($request, Kernel::SUB_REQUEST);
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

    /**
     * Lee la Url de la petición actual, extrae el módulo/controlador/acción/parametros
     * y los almacena en los atributos de la clase.
     * 
     * Es llamado en el evento kumbia.request
     * 
     * @throws NotFoundException 
     */
    public function parseUrl()
    {
        $controller = 'index'; //controlador por defecto si no se especifica.
        $action = 'index'; //accion por defecto si no se especifica.
        $moduleUrl = '/';
        $params = array(); //parametros de la url, de existir.
        //obtenemos la url actual de la petición.
        $currentUrl = '/' . trim(App::getRequest()->getRequestUrl(), '/');

        list($moduleUrl, $module, $currentUrl) = App::get('app.kernel')->getModule($currentUrl);

        if (!$moduleUrl || !$module) {
            throw new NotFoundException(sprintf("La ruta \"%s\" no concuerda con ningún módulo ni controlador en la App", $currentUrl), 404);
        }

        if ($url = explode('/', trim(substr($currentUrl, strlen($moduleUrl)), '/'))) {

            //ahora obtengo el controlador
            if (current($url)) {
                //si no es un controlador lanzo la excepcion
                $controller = current($url);
                next($url);
            }
            //luego obtenemos la acción
            if (current($url)) {
                $action = current($url);
                next($url);
            }
            //por ultimo los parametros
            if (current($url)) {
                $params = array_slice($url, key($url));
            }
        }
        App::setContext(array(
            'module' => $module,
            'module_url' => $moduleUrl,
            'controller' => $controller,
            'action' => $action,
            'parameters' => $params,
        ));
    }

    public function createUrl($url, $baseUrl = true)
    {
        $url = explode(':', $url);
        if (count($url) > 1) {
            if (!$route = array_search($url[0], App::routes())) {
                throw new NotFoundException("No Existe el módulo {$url[0]}, no se pudo crear la url");
            }
            $url = ltrim(trim($route, '/') . '/' . $url[1], '/');
        } else {
            $url = ltrim($url[0], '/');
        }
        //si se usa locale, lo añadimos a la url.
        App::getRequest()->getLocale() && $url = App::getRequest()->getLocale() . '/' . $url;
        return $baseUrl ? PUBLIC_PATH . $url : $url;
    }

}