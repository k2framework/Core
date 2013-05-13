<?php

namespace K2\Kernel;

use K2\Kernel\AppContext;
use K2\Di\Container\Container;
use K2\Kernel\KernelInterface;
use K2\Kernel\Event\K2Events;
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
abstract class Kernel implements KernelInterface
{

    /**
     * Arreglo con los namespaces definidos en la aplicación
     * @var array 
     */
    protected $modules;

    /**
     * Arreglo con los prefijos de las rutas para los modulos usados en la aplicación
     * @var array 
     */
    protected $routes;

    /**
     * Objeto contenedor de servicios
     *  
     * @var Container
     */
    protected $container;

    /**
     * Objeto Request
     *
     * @var Request 
     */
    protected $request;

    /**
     * Objeto despachador de eventos
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Indica si la aplicación está ó no en producción
     * @var boolean 
     */
    protected $production;

    /**
     * ruta hacia la carpeta app del fw 
     * @var string
     */
    protected $appPath;

    /**
     * Contiene los locales permitidos en la App
     * @var array 
     */
    protected $locales;

    /**
     * Constructor de la clase. 
     * 
     * La instancia del kernel se crea en proyecto/public/index.php
     * 
     * @param boolean $production indica si estamos en producción ó no.
     */
    public function __construct($production = FALSE)
    {
        ob_start(); //arrancamos el buffer de salida.
        $this->production = $production;

        App::getLoader()->add(null, $this->getAppPath() . '/modules/');

        ExceptionHandler::handle($this);

        if ($production) {
            error_reporting(0);
            ini_set('display_errors', 'Off');
        } else {
            error_reporting(-1);
            ini_set('display_errors', 'On');
        }

        $this->routes = $this->registerRoutes();
    }

    /**
     * Inicializa las clases basicas del fw.
     * 
     * AppContext, Container, Inyector de dependencias, Dispatcher, etc.
     *  
     */
    public function init(Request $request)
    {
        //creamos la instancia del AppContext
        $context = new AppContext($this->production, $this->getAppPath(), $this->modules, $this->routes);
        //iniciamos el container
        $this->initContainer();
        //asignamos el kernel al container como un servicio
        $this->container->setInstance('app.kernel', $this);
        //si se usan locales los añadimos.
        if (isset($this->container['config']['locales'])) {
            $this->locales = explode(',', $this->container['config']['locales']);
        }
        //iniciamos el dispatcher con esa config
        $this->initDispatcher();
        //inicializamos los modulos de la app.
        $this->initModules();
        //seteamos el contexto de la aplicación como servicio
        $this->container->setInstance('app.context', $context);
        //establecemos el Request en el AppContext
        $context->setRequest($request);
    }

    public function execute(Request $request, $type = Kernel::MASTER_REQUEST)
    {
        try {
            //verificamos el tipo de petición
            if (self::MASTER_REQUEST === $type) {
                return $this->_execute($request, $type);
            } else {
                //almacenamos en una variable temporal el request
                //original. y actualizamos el AppContext.
                //tambien el tipo de request
                $originalRequest = $this->request;
                $originalRequestType = $this->container->get('app.context')
                        ->getRequestType();
                $this->container->get('app.context')
                        ->setRequest($request)
                        ->setRequestType($type);

                $response = $this->_execute($request, $type);

                //Luego devolvemos el request original al kernel,
                //al AppContext, y el tipo de request
                $this->request = $originalRequest;
                $this->container->setInstance('request', $originalRequest);
                $this->container->get('app.context')
                        ->setRequest($originalRequest)
                        ->setRequestType($originalRequestType);

                return $response;
            }
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    private function _execute(Request $request, $type = Kernel::MASTER_REQUEST)
    {
        $this->request = $request;

        if (!$this->container) { //si no se ha creado el container lo creamos.
            $this->init($request);
            $this->container->get('app.context')->setRequestType($type);
        }
        //agregamos el request al container
        $this->container->setInstance('request', $this->request);

        //ejecutamos el evento request
        $this->dispatcher->dispatch(K2Events::REQUEST, $event = new RequestEvent($request));

        if (!$event->hasResponse()) {

            //creamos el resolver.
            $resolver = new ControllerResolver($this->container);

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
    private function createResponse(ControllerResolver $resolver)
    {
        $controller = $resolver->getController();
        //como la acción no devolvió respuesta, debemos
        //obtener la vista y el template establecidos en el controlador
        //para pasarlos al servicio view, y este construya la respuesta
        //llamamos al render del servicio "view" y esté nos devolverá
        //una instancia de response con la respuesta creada
        return $this->container->get('view')->render($controller->getView(),array(
                    'response' => $controller->getResponse(),
                    'time' => $controller->getCache(),
                    'params' => get_object_vars($controller),
                ));
    }

    private function exception(\Exception $e)
    {
        $event = new ExceptionEvent($e, $this->request);
        $this->dispatcher->dispatch(K2Events::EXCEPTION, $event);

        if ($event->hasResponse()) {
            return $this->response($event->getResponse());
        }

        if ($this->production) {
            return ExceptionHandler::createException($e);
        }

        throw $e;
    }

    private function response(Response $response)
    {
        $event = new ResponseEvent($this->request, $response);
        //ejecutamos el evento response.
        $this->dispatcher->dispatch(K2Events::RESPONSE, $event);
        //retornamos la respuesta
        return $event->getResponse();
    }

    /**
     *
     * @return boolean 
     */
    public function isProduction()
    {
        return $this->production;
    }

    /**
     * clase abstracta que está implementada en el AppKernel de la carpeta app
     * del proyecto, donde se especifican las rutas y los modulos que trabajará 
     * la aplicación, permite instalar librerias, etc.
     */
    abstract protected function registerModules();

    /**
     * clase abstracta que está implementada en el AppKernel de la carpeta app
     * del proyecto, donde se especificarán en un arreglo los prefijos de rutas
     * que tendran los modulos de la aplicación.
     */
    abstract protected function registerRoutes();

    /**
     * devuelve la ruta a la carpeta app del proyecto.
     * @return string 
     */
    public function getAppPath()
    {
        if (!$this->appPath) {
            $r = new \ReflectionObject($this);
            $this->appPath = dirname($r->getFileName()) . '/';
        }
        return $this->appPath;
    }

    /**
     * Esta función inicializa el contenedor de servicios.
     */
    protected function initContainer(array $config = array())
    {
        $this->container = new Container();
        $this->container->setParameter('app_dir', $this->getAppPath());
        App::setContainer($this->container);
        $this->readConfig();
    }

    protected function initModules()
    {
        $this->modules = (array) $this->registerModules();
        foreach ($this->modules as $name => $module) {
            $module->setContainer($this->container);
            $module->setEventDispatcher($this->dispatcher);
            $module->init();
            if (null !== $module->extend()) {
                if (null === $parent = $this->getModules($module->extend())) {
                    throw new InvalidArgumentException("No exite el módulo {$module->extend()} para extenderlo");
                }
                $parent->setChildren($module);
            }
        }
    }

    /**
     * Inicializa el despachador de eventos
     */
    protected function initDispatcher()
    {
        $this->dispatcher = new EventDispatcher($this->container);
        $this->container->setInstance('event.dispatcher', $this->dispatcher);
    }

    protected function readConfig()
    {
        $config = Config\Reader::read('config');

        foreach ($config as $section => $values) {
            $this->container->setParameter($section, $values);
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
                $this->request->setLocale($locale);
                return $this->getModule('/' . join('/', $_url), false);
            } else {
                $this->request->setLocale($this->locales[0]);
            }
        }

        $routes = array_keys($this->routes);

        usort($routes, function($a, $b) {
                    return strlen($a) > strlen($b) ? -1 : 1;
                }
        );

        foreach ($routes as $route) {
            if (0 === strpos($url, $route)) {
                if ('/' === $route) {
                    return array($route, $this->getModules($this->getRoutes('/')), $url);
                } elseif ('/' === substr($url, strlen($route), 1) || strlen($url) === strlen($route)) {
                    return array($route, $this->getModules($this->getRoutes($route)), $url);
                }
            }
        }
        return false;
    }

    /**
     * devuelve los modulos registrados en el proyecto
     * @return array 
     */
    public function getModules($name = null)
    {
        if ($name) {
            foreach ($this->modules as $module) {
                if ($name === $module->getName()) {
                    return $module;
                }
            }
            return null;
        } else {
            return $this->modules;
        }
    }

    /**
     * devuelve las rutas registradas en el proyecto
     * @param string $route si se suministra un prefijo, devuelve solo
     * el valor de la ruta para ese prefijo.
     * @return array|string|null 
     */
    public function getRoutes($route = null)
    {
        if ($route) {
            if (isset($this->routes[$route])) {
                foreach ($this->modules as $module) {
                    if ($this->routes[$route] === $module->getName()) {
                        return $this->routes[$route];
                    }
                }
            }
            return null;
        } else {
            return $this->routes;
        }
    }

}