<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Loader\Autoload;
use KumbiaPHP\Kernel\KernelInterface;
use KumbiaPHP\Kernel\Event\RequestEvent;
use KumbiaPHP\Kernel\AppContext;
use KumbiaPHP\Kernel\Controller\ControllerResolver;
use KumbiaPHP\Kernel\Event\KumbiaEvents;
use KumbiaPHP\EventDispatcher\EventDispatcher;
use KumbiaPHP\Kernel\Event\ControllerEvent;
use KumbiaPHP\Kernel\Event\ResponseEvent;
use KumbiaPHP\Kernel\Config\ConfigContainer;
use KumbiaPHP\Di\DependencyInjection;
use KumbiaPHP\Di\Container\Container;
use KumbiaPHP\Di\Definition\DefinitionManager;
use KumbiaPHP\Kernel\Exception\ExceptionHandler;

require_once __DIR__ . '/../Loader/Autoload.php';
require_once __DIR__ . '/KernelInterface.php';

Autoload::register();

/**
 * Description of Kernel
 *
 * @author manuel
 */
abstract class Kernel implements KernelInterface
{

    protected $namespaces;
    protected $routes;

    /**
     *
     * @var DependencyInjection
     */
    protected $di;

    /**
     *  
     * @var Container
     */
    protected static $container;

    /**
     *
     * @var Request 
     */
    protected $request;

    /**
     *  @var EventDispatcher
     */
    protected $dispatcher;

    /**
     *
     * @var boolean 
     */
    protected $production;

    /**
     *
     * @var string
     */
    protected $appPath;

    public function __construct($production = FALSE)
    {
        ob_start(); //arrancamos el buffer de salida.
        $this->production = $production;

        Autoload::registerDirectories(
                $this->namespaces = $this->registerNamespaces()
        );

        if ($production) {
            error_reporting(0);
            ini_set('display_errors', 'Off');
        } else {
            error_reporting(-1);
            ini_set('display_errors', 'On');
        }
        ExceptionHandler::handle($this);

        $this->routes = $this->registerRoutes();
    }

    protected function init()
    {
        //creamos la instancia del AppContext
        $context = new AppContext($this->request, $this->production, $this->getAppPath(), $this->routes, $this->namespaces);
        //leemos la config de la app
        $config = new ConfigContainer($context);
        //iniciamos el container con esa config
        $this->initContainer($config->getConfig());
        //asignamos el kernel al container como un servicio
        self::$container->set('app.kernel', $this);
        //iniciamos el dispatcher con esa config
        $this->initDispatcher($config->getConfig());

        //le asignamos el servicio session al request
        $this->request->setSession(self::$container->get('session'));

        //agregamos el request al container
        self::$container->set('request', $this->request);

        self::$container->set('app.context', $context);
    }

    //put your code here
    public function execute(Request $request)
    {
        $this->request = $request;

        if (!self::$container) { //si no se ha creado el container lo creamos.
            $this->init();
        } else {//si ya se creó el container solo actualizamos el app.context con el nuevo request
            self::$container->get('app.context')->setRequest($this->request);
        }

        //ejecutamos el evento request
        //$this->dispatcher->dispatch(KumbiaEvents::REQUEST, new RequestEvent($request));

        $resolver = new ControllerResolver(self::$container);

        //obtenemos la instancia del controlador, el nombre de la accion
        //a ejecutar, y los parametros que recibirá dicha acción
        list($controller, $action, $params) = $resolver->getController($request);

        //ejecutamos el evento controller.
        $this->dispatcher
                ->dispatch(KumbiaEvents::CONTROLLER, new ControllerEvent($request, array($controller, $action)));
        //ejecutamos la acción de controlador pasandole los parametros.
        $response = $resolver->executeAction($action, $params);

        if (!$response instanceof Response) {

            //si no es una instancia de KumbiaPHP\Kernel\Controller\Controller
            //lanzamos una excepción
            if (!$controller instanceof \KumbiaPHP\Kernel\Controller\Controller) {
                throw new \LogicException(sprintf("El controlador debe retornar una Respuesta"));
            } else {
                //como la acción no devolvió respuesta, debemos
                //obtener la vista y el template establecidos en el controlador
                //para pasarlos al servicio view, y este construya la respuesta
                $view = $resolver->getView();
                $template = $resolver->getTemplate();
                $properties = $resolver->getPublicProperties(); //nos devuelve las propiedades publicas del controlador
                //llamamos al render del servicio "view" y esté nos devolverá
                //una instancia de response con la respuesta creada
                $response = self::$container->get('view')->render($template, $view, $properties);
            }
        }

        //ejecutamos el evento response.
        $this->dispatcher->dispatch(KumbiaEvents::RESPONSE, new ResponseEvent($request, $response));
        //retornamos la respuesta
        return $response;
    }

    /**
     *
     * @return \KumbiaPHP\Di\Container\ContainerInterface 
     */
    public static function getContainer()
    {
        return self::$container;
    }

    abstract protected function registerNamespaces();

    abstract protected function registerRoutes();

    private function getAppPath()
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
    protected function initContainer(Parameters $config)
    {

        $definitions = new DefinitionManager();

        foreach ($config->get('services')->all() as $id => $configs) {
            $definitions->addService(new \KumbiaPHP\Di\Definition\Service($id, $configs));
        }

        foreach ($config->get('parameters')->all() as $id => $value) {
            $definitions->addParam(new \KumbiaPHP\Di\Definition\Parameter($id, $value));
        }

        $this->di = new DependencyInjection();

        self::$container = new Container($this->di, $definitions);
    }

    protected function initDispatcher(Parameters $config)
    {
        $this->dispatcher = new EventDispatcher(self::$container);
        foreach ($config->get('services')->all() as $service => $params) {
            if (isset($params['listen'])) {
                foreach ($params['listen'] as $method => $event) {
                    $this->dispatcher->addListener($event, array($service, $method));
                }
            }
        }
    }

}