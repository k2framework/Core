<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Loader\Autoload;
use KumbiaPHP\Kernel\AppContext;
use KumbiaPHP\Di\DependencyInjection;
use KumbiaPHP\Di\Container\Container;
use KumbiaPHP\Kernel\KernelInterface;
use KumbiaPHP\Kernel\Event\KumbiaEvents;
use KumbiaPHP\Kernel\Event\RequestEvent;
use KumbiaPHP\Kernel\Event\ResponseEvent;
use KumbiaPHP\Kernel\Config\ConfigReader;
use KumbiaPHP\Kernel\Event\ExceptionEvent;
use KumbiaPHP\Kernel\Event\ControllerEvent;
use KumbiaPHP\Di\Definition\DefinitionManager;
use KumbiaPHP\EventDispatcher\EventDispatcher;
use KumbiaPHP\Kernel\Exception\ExceptionHandler;
use KumbiaPHP\Kernel\Controller\ControllerResolver;

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
    protected $namespaces;

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
    protected static $container;

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

        Autoload::registerDirectories(
                $this->namespaces = $this->registerNamespaces()
        );

        Autoload::register();

        //ExceptionHandler::handle($this);

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
        $this->request = $request;
        //creamos la instancia del AppContext
        $context = new AppContext($this->request, $this->production, $this->getAppPath(), $this->routes, $this->namespaces);
        //leemos la config de la app
        $config = new ConfigReader($context);
        //iniciamos el container con esa config
        $this->initContainer($config);
        //asignamos el kernel al container como un servicio
        self::$container->set('app.kernel', $this);
        //iniciamos el dispatcher con esa config
        $this->initDispatcher($config);

        //seteamos el contexto de la aplicación como servicio
        self::$container->set('app.context', $context);
        //le asignamos el servicio AppContext al request
        $this->request->setAppContext($context);

        //agregamos el request al container
        self::$container->set('request', $this->request);
    }

    public function execute(Request $request)
    {
        try {
            return $this->_execute($request);
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    private function _execute(Request $request)
    {
        if (!self::$container) { //si no se ha creado el container lo creamos.
            $this->init($request);
        } else {//si ya se creó el container solo actualizamos el app.context con el nuevo request
            $this->request = $request;
            self::$container->get('app.context')->setRequest($this->request);
        }

        //ejecutamos el evento request
        $this->dispatcher->dispatch(KumbiaEvents::REQUEST, $event = new RequestEvent($request));

        if (!$event->hasResponse()) {

            //si el evento no devuelve una respuesta, ejecutamos el controlador.
            $resolver = new ControllerResolver(self::$container);

            //obtenemos la instancia del controlador, el nombre de la accion
            //a ejecutar, y los parametros que recibirá dicha acción
            list($controller, $action, $params) = $resolver->getController($request);

            $event = new ControllerEvent($request, array($controller, $action, $params));

            //ejecutamos el evento controller.
            $this->dispatcher->dispatch(KumbiaEvents::CONTROLLER, $event);

            //ejecutamos la acción de controlador pasandole los parametros.
            $response = $resolver->executeAction($event);

            if (!$response instanceof Response) {
                //como la acción no devolvió respuesta, debemos
                //obtener la vista y el template establecidos en el controlador
                //para pasarlos al servicio view, y este construya la respuesta
                $view = $resolver->getParamValue('view');
                $template = $resolver->getParamValue('template');
                $cache = $resolver->getParamValue('cache');
                $properties = $resolver->getPublicProperties(); //nos devuelve las propiedades publicas del controlador
                //llamamos al render del servicio "view" y esté nos devolverá
                //una instancia de response con la respuesta creada
                /* @var $response Response */
                $response = self::$container->get('view')->render($template, $view, $properties, $cache);
            }
        } else {
            $response = $event->getResponse();
        }

        return $this->response($response);
    }

    private function exception(\Exception $e)
    {
        $event = new ExceptionEvent($e, $this->request);
        $this->dispatcher->dispatch(KumbiaEvents::EXCEPTION, $event);
        
        if ( $event->hasResponse() ){
            return $this->response($event->getResponse());
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

    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * clase abstracta que está implementada en el AppKernel de la carpeta app
     * del proyecto, donde se especifican las rutas y los namespaces que trabajará 
     * la aplicación, permite instalar librerias, etc.
     */
    abstract protected function registerNamespaces();

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
     * @param Collection $reader toda la configuracion de los archivos de config
     * de cada lib y modulo compilados en uno solo.
     */
    protected function initContainer(ConfigReader $reader)
    {

        $definitions = new DefinitionManager();

        foreach ($reader->getConfig()->get('services')->all() as $id => $configs) {
            $definitions->addService(new \KumbiaPHP\Di\Definition\Service($id, $configs));
        }

        foreach ($reader->getConfig()->get('parameters')->all() as $id => $value) {
            $definitions->addParam(new \KumbiaPHP\Di\Definition\Parameter($id, $value));
        }

        $this->di = new DependencyInjection();

        self::$container = new Container($this->di, $definitions);
    }

    /**
     * Inicializa el despachador de eventos
     * @param Collection $reader config de todo el proyecto.
     */
    protected function initDispatcher(ConfigReader $reader)
    {
        $this->dispatcher = new EventDispatcher(self::$container);
        foreach ($reader->getConfig()->get('services')->all() as $service => $params) {
            if (isset($params['listen'])) {
                foreach ($params['listen'] as $method => $event) {
                    $this->dispatcher->addListener($event, array($service, $method));
                }
            }
        }
    }

}