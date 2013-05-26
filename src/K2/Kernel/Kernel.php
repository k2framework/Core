<?php

namespace K2\Kernel;

use K2\Kernel\App;
use K2\Kernel\Event\K2Events;
use K2\Di\Container\Container;
use K2\Kernel\Event\RequestEvent;
use K2\Kernel\Event\ResponseEvent;
use K2\Kernel\Event\ExceptionEvent;
use K2\EventDispatcher\EventDispatcher;
use K2\Kernel\Exception\ExceptionHandler;
use K2\Kernel\Controller\ControllerResolver;

/**
 * Kernel del FW
 *
 * @author manuel
 */
class Kernel
{

    const MASTER_REQUEST = 1;
    const SUB_REQUEST = 2;

    /**
     * Objeto despachador de eventos
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Contiene los locales permitidos en la App
     * @var array 
     */
    protected $locales;
    protected $hasException = false;

    /**
     * Constructor de la clase. 
     * 
     * La instancia del kernel se crea en proyecto/public/index.php
     * 
     * @param boolean $showExceptions indica si se muestran las excepciones en producción ó no
     */
    public function __construct($showExceptions = false)
    {
        App::getLoader()->add(null, APP_PATH . '/modules/');

        $this->initContainer();

        $this->initDispatcher();

        $this->initModules();

        $this->readConfig();

        App::get('container')->setParameter('show_exceptions', $showExceptions);
    }

    /**
     * Metodó que ejecuta todo el proceso de la ejecucion de la petición.
     * @param Request $request objeto que contiene toda la info de la petición 
     * @param int $type indica si la petición es la original ó es una sub petición. 
     * @return Response objeto respuesta
     * @throws \LogicException excepcion si no se puede devolver una respuesta
     */
    public function execute(Request $request, $type = self::MASTER_REQUEST)
    {
        $request->setType($type);
        try {
            App::setRequest($request);
            $response = $this->_execute($request, $type);
            App::terminate();
            return $response;
        } catch (\Exception $e) {
            $this->hasException = true;
            return $this->exception($e);
        }
    }

    private function _execute(Request $request, $type = Kernel::MASTER_REQUEST)
    {
        $this->dispatcher->dispatch(K2Events::REQUEST, $event = new RequestEvent($request));

        if (!$event->hasResponse()) {
            //creamos el resolver.
            $resolver = new ControllerResolver(App::get('container'));

            //ejecutamos la acción de controlador pasandole los parametros.
            $response = $resolver->executeAction();
            if (!$response instanceof Response) {
                $response = $this->createResponse($resolver);
            }
        } else {
            $response = $event->getResponse();
        }

        return $this->response($response);
    }

    /**
     * Crea una respuesta a partir de  la vista y template seleccionados
     * en el controlador.
     * @param ControllerResolver $resolver
     * @return Response
     */
    public function createResponse(ControllerResolver $resolver)
    {
        $controller = $resolver->getController();
        //como la acción no devolvió respuesta, debemos
        //obtener la vista y el template establecidos en el controlador
        //para pasarlos al servicio view, y este construya la respuesta
        //llamamos al render del servicio "view" y esté nos devolverá
        //una instancia de response con la respuesta creada
        return App::get('view')->render($controller->getView(), array(
                    'response' => $controller->getResponse(),
                    'time' => $controller->getCache(),
                    'params' => get_object_vars($controller),
        ));
    }

    private function exception(\Exception $e)
    {
        $event = new ExceptionEvent($e, App::getRequest());
        $this->dispatcher->dispatch(K2Events::EXCEPTION, $event);

        if ($event->hasResponse()) {
            return $this->response($event->getResponse());
        }
        
        throw $e;
    }

    private function response(Response $response)
    {
        $event = new ResponseEvent(App::getRequest(), $response);
        //ejecutamos el evento response.
        $this->dispatcher->dispatch(K2Events::RESPONSE, $event);
        //retornamos la respuesta
        return $event->getResponse();
    }

    /**
     * Esta función inicializa el contenedor de servicios.
     */
    protected function initContainer(array $config = array())
    {
        App::setContainer($container = new Container());

        $container->setInstance('app.kernel', $this);
    }

    protected function initModules()
    {
        $container = App::get('container');
        foreach (App::modules() as $name => $config) {
            if (isset($config['services'])) {
                $container->setFromArray($config['services']);
            }

            if (isset($config['listeners'])) {
                foreach ($config['listeners'] as $event => $listeners) {
                    foreach ($listeners as $priority => $listener) {
                        $this->dispatcher->addListener($event, $listener, $priority);
                    }
                }
            }

            if (isset($config['parameters'])) {
                $container->setParameter($name, $config['parameters']);
            }
        }
        //esto es aparte para que se cargen todos los servicios y parametros
        foreach (App::modules() as $name => $config) {
            if (is_callable($config['init'])) {
                call_user_func($config['init'], $container);
            }
        }
    }

    /**
     * Inicializa el despachador de eventos
     */
    protected function initDispatcher()
    {
        $this->dispatcher = new EventDispatcher(App::get('container'));
        App::get('container')->setInstance('event.dispatcher', $this->dispatcher);
    }

    protected function readConfig()
    {
        $config = Config\Reader::read('config');

        foreach ($config as $section => $values) {
            App::get('container')->setParameter($section, $values);
        }

        if (isset($config['config']['locales'])) {
            $this->locales = explode(',', $config['config']['locales']);
        }
    }

    /**
     * Devuelve el posible módulo a partir de la Url recibida como parametro.
     * @param string $url
     * @param boolean $recursive
     * @return array ($moduleUrl, $moduleName, $currentUrl)
     */
    public function getModule($url, $recursive = true)
    {
        if (count($this->locales) && $recursive) {
            $_url = explode('/', trim($url, '/'));
            $locale = array_shift($_url);
            if (in_array($locale, $this->locales)) {
                App::getRequest()->setLocale($locale);
                return $this->getModule('/' . join('/', $_url), false);
            } else {
                App::getRequest()->setLocale($this->locales[0]);
            }
        }

        $routes = array_keys(App::routes());

        usort($routes, function($a, $b) {
                    return strlen($a) > strlen($b) ? -1 : 1;
                }
        );

        foreach ($routes as $route) {
            if (0 === strpos($url, $route)) {
                if ('/' === $route) {
                    return array($route, App::getModule(App::getRoute('/')), $url);
                } elseif ('/' === substr($url, strlen($route), 1) || strlen($url) === strlen($route)) {
                    return array($route, App::getModule(App::getRoute($route)), $url);
                }
            }
        }
        return false;
    }

    public function hasException()
    {
        return $this->hasException;
    }

}