<?php

namespace K2\Kernel;

use K2\Kernel\Request;
use K2\Di\Container\Container;
use Exception\NotFoundException;
use Composer\Autoload\ClassLoader;
use K2\Security\Auth\User\UserInterface;

class App
{

    /**
     *
     * @var Container
     */
    protected static $container;

    /**
     *
     * @var ClassLoader 
     */
    protected static $loader;
    protected static $modules;
    protected static $routes;
    protected static $request = array();
    protected static $context = array();

    /**
     * Contiene instancias de servicios que están atados a un request especifico
     * @var array
     */
    protected static $services = array();

    /**
     * Contiene los nombres de los servicios que se crearán por cada request creado
     * @var array 
     */
    protected static $requestServices = array();

    /**
     * Contiene las definiciones de los servicios, parametros y escuchas de 
     * eventos de los modulos de la app.
     * @var array 
     */
    protected static $definitions = array(
        'services' => array(),
        'parameters' => array(),
        'listeners' => array(),
        'twig_extensions' => array(),
    );

    /**
     * 
     * @param Container $container
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    /**
     * 
     * @param ClassLoader $loader
     */
    public static function setLoader(ClassLoader $loader)
    {
        self::$loader = $loader;
    }

    /**
     * 
     * @return ClassLoader
     */
    public static function getLoader()
    {
        return self::$loader;
    }

    /**
     * Devuelve un servicio del container
     * 
     * @param string $service Nombre del Servicio 
     * @return object
     */
    public static function get($service)
    {
        return self::$container->get($service);
    }

    /**
     * Devuelve un parametro del contenedor 
     * @param string $parameter
     * @return mixed
     */
    public static function getParameter($parameter)
    {
        return self::$container->getParameter($parameter);
    }

    /**
     * Agregamos un objeto request a la cola de peticiones.
     * @return Request
     */
    public static function setRequest(Request $request)
    {
        //algunos servicios deben ser instancias distintas dependiendo de la petición,
        //ya que contienen datos referentes a la petición en curso, por lo tanto
        //se debe crear una instancia por cadá subpetición hecha al kernel.
        if ($request->getType() == Kernel::SUB_REQUEST) {
            foreach (static::$requestServices as $key) {
                if (static::$container->hasInstance($key)) {
                    static::$services[$key][] = self::$container[$key];
                    static::$container->removeInstance($key);
                }
            }
        }

        static::$request[] = $request;

        static::parseUrl($request->getRequestUrl());

        return $request;
    }

    /**
     * Devuelve el request actual de la cola de peticiones
     * @return Request|null
     */
    public static function getRequest()
    {
        return end(static::$request);
    }

    /**
     * Elimina el request y el contexto en uso de la cola de peticiones
     */
    public static function terminate()
    {
        unset(static::$context[static::getRequest()->getRequestUrl()]);
        array_pop(static::$request);
        //por cada servicio agregado a la cola en una subpetición, 
        //debemos sacarlo de la cola al final, y pasarlo al container
        foreach (static::$services as $key => $instances) {
            self::$container->setInstance($key, array_pop(static::$services[$key]));
        }
    }

    /**
     * Lee la Url de la petición actual, extrae el módulo/controlador/acción/parametros
     * y los almacena en los atributos de la clase.
     * @param \K2\Kernel\Request $request
     * @throws NotFoundException
     */
    protected static function parseUrl($url)
    {
        $controller = 'index'; //controlador por defecto si no se especifica.
        $action = 'index'; //accion por defecto si no se especifica.
        $moduleUrl = '/';
        $params = array(); //parametros de la url, de existir.
        //obtenemos la url actual de la petición.
        $currentUrl = '/' . trim($url, '/');

        list($moduleUrl, $module, $currentUrl) = static::get('app.kernel')->getModule($currentUrl);

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
        static::setContext(array(
            'module' => $module,
            'module_url' => $moduleUrl,
            'controller' => $controller,
            'action' => $action,
            'parameters' => $params,
        ));
    }

    public static function setContext(array $data)
    {
        $uri = static::getRequest()->getRequestUrl();
        if (isset(static::$context[$uri])) {
            static::$context[$uri] = array_merge(static::$context[$uri], $data);
        } else {
            static::$context[$uri] = $data;
        }
    }

    public static function getContext($index = null)
    {
        if (null === $index) {
            return static::$context[static::getRequest()->getRequestUrl()];
        } else {
            if (isset(static::$context[static::getRequest()->getRequestUrl()])) {
                $context = static::$context[static::getRequest()->getRequestUrl()];
                return isset($context[$index]) ? $context[$index] : null;
            }
        }
    }

    /**
     * 
     * @return UserInterface
     */
    public static function getUser()
    {
        if (!is_object($token = self::$container->get('security')->getToken())) {
            return null;
        }
        return $token->getUser();
    }

    /**
     * 
     * @return string
     */
    public static function requestUrl()
    {
        return self::getRequest()->getRequestUrl();
    }

    public static function modules(array $modules = null)
    {
        if (null === $modules) {
            return static::$modules;
        } else {
            foreach ($modules as $index => $module) {

                static::addDefinitions('services', $module);
                static::addDefinitions('parameters', $module);
                static::addDefinitions('listeners', $module);
                static::addDefinitions('twig_extensions', $module);

                static::$modules[$module['name']] = $module + array('init' => null);
                //si el indice no es numerico, agregamos el mismo a las rutas
                if (!is_numeric($index)) {
                    static::$routes[$index] = $module['name'];
                }
            }
        }
    }

    public static function routes()
    {
        return static::$routes;
    }

    public static function getModule($name, $index = null, $throw = true)
    {
        if (isset(static::$modules[$name])) {
            if (null !== $index) {
                if (array_key_exists(static::$modules[$name], $index)) {
                    return static::$modules[$name][$index];
                }
                if ($throw) {
                    throw new \InvalidArgumentException(sprintf('No existe el indice %s en la configuración del Módulo %s', $index, $name));
                }

                return null;
            } else {
                return static::$modules[$name];
            }
        }
        if ($throw) {
            throw new \InvalidArgumentException(sprintf('No existe el Módulo %s', $name));
        }

        return null;
    }

    public static function getRoute($route, $throw = true)
    {
        if (isset(static::$routes[$route])) {
            return static::$routes[$route];
        }
        if ($throw) {
            throw new \InvalidArgumentException(sprintf('No existe la ruta %s', $route));
        }

        return null;
    }

    public static function prefix($module, $throw = true)
    {
        if (!in_array($module, static::$routes)) {
            if ($throw) {
                throw new \InvalidArgumentException(sprintf('No existe un prefijo de ruta para el módulo %s', $module));
            }
            return null;
        }

        return array_search($module, static::$routes);
    }

    /**
     * Añade un servicio para que su instancia sea creada por acada request hecho 
     * al kernel en una petición
     * @param string $idService nombre del servicio
     */
    public static function addSerciveToRequest($idService)
    {
        static::$requestServices[$idService] = $idService;
    }

    protected static function addDefinitions($key, array &$data = array())
    {
        if (isset($data[$key])) {
            static::$definitions[$key] = array_merge(static::$definitions[$key], $data[$key]);
            unset($data[$key]);
        }
    }

    public static function definitions($key = null, $unset = false)
    {
        if (null == $key) {
            if (!$unset) {
                return static::$definitions;
            }
            $def = static::$definitions;
            static::$definitions = array(
                'services' => array(),
                'parameters' => array(),
                'listeners' => array(),
                'twig_extensions' => array(),
            );
            return $def;
        }

        if (isset(static::$definitions[$key])) {
            $def = static::$definitions[$key];
            if($unset){
                static::$definitions[$key] = array();
            }
            return $def;
        }
    }

}
