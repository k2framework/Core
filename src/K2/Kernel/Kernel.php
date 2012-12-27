<?php

namespace K2\Kernel;

use K2\Kernel\AppContext;
use K2\Di\DependencyInjection;
use K2\Di\Container\Container;
use K2\Kernel\KernelInterface;
use K2\Kernel\Event\KumbiaEvents;
use K2\Kernel\Event\RequestEvent;
use K2\Kernel\Event\ResponseEvent;
use K2\Kernel\Config\ConfigReader;
use K2\Kernel\Event\ExceptionEvent;
use K2\Kernel\Event\ControllerEvent;
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
     * Objeto inyector de dependencias
     *
     * @var DependencyInjection
     */
    protected $di;

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

        App::getLoader()->add(null, $this->modules = $this->registerModules());

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
        //leemos la config de la app
        $config = new ConfigReader($context);
        //iniciamos el container con esa config
        $this->initContainer($config->getConfig());
        //asignamos el kernel al container como un servicio
        $this->container->setInstance('app.kernel', $this);
        //iniciamos el dispatcher con esa config
        $this->initDispatcher();
        //seteamos el contexto de la aplicación como servicio
        $this->container->setInstance('app.context', $context);
        //si se usan locales los añadimos.
        if (isset($this->container['config']['locales'])) {
            $context->setLocales($this->container['config']['locales']);
        }
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
        $this->dispatcher->dispatch(KumbiaEvents::REQUEST, $event = new RequestEvent($request));

        if (!$event->hasResponse()) {

            //creamos el resolver.
            $resolver = new ControllerResolver($this->container);
            //obtenemos la instancia del controlador, el nombre de la accion
            //a ejecutar, y los parametros que recibirá dicha acción a traves del método
            //getController del $resolver y lo pasamos al ControllerEvent
            $event = new ControllerEvent($request, $resolver->getController($request));
            //ejecutamos el evento controller.
            $this->dispatcher->dispatch(KumbiaEvents::CONTROLLER, $event);

            //ejecutamos la acción de controlador pasandole los parametros.
            $response = $resolver->executeAction($event);
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
        //como la acción no devolvió respuesta, debemos
        //obtener la vista y el template establecidos en el controlador
        //para pasarlos al servicio view, y este construya la respuesta
        //llamamos al render del servicio "view" y esté nos devolverá
        //una instancia de response con la respuesta creada
        return $this->container->get('view')->render(array(
                    'template' => $resolver->callMethod('getTemplate'),
                    'view' => $resolver->callMethod('getView'),
                    'response' => $resolver->callMethod('getResponse'),
                    'time' => $resolver->callMethod('getCache'),
                    'params' => $resolver->getPublicProperties(), //nos devuelve las propiedades publicas del controlador
                ));
    }

    private function exception(\Exception $e)
    {
        $event = new ExceptionEvent($e, $this->request);
        $this->dispatcher->dispatch(KumbiaEvents::EXCEPTION, $event);

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
        $this->dispatcher->dispatch(KumbiaEvents::RESPONSE, $event);
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
     * @param array $config toda la configuracion de los archivos de config
     * de cada lib y modulo compilados en uno solo.
     */
    protected function initContainer(array $config = array())
    {
        $config['parameters']['app_dir'] = $this->getAppPath();

        $this->di = new DependencyInjection();

        $this->container = new Container($this->di, $config);
        App::setContainer($this->container);
    }

    /**
     * Inicializa el despachador de eventos
     */
    protected function initDispatcher()
    {
        $this->dispatcher = new EventDispatcher($this->container);
        $this->container->setInstance('event.dispatcher', $this->dispatcher);
    }

}