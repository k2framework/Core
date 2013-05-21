<?php

namespace K2\Kernel;

use K2\Kernel\Request;
use K2\Kernel\AppContext;
use Composer\Autoload\ClassLoader;
use K2\Di\Container\Container;
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
    protected static $services = array();
    protected static $checkServices = array('twig');

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
            foreach (static::$checkServices as $key) {
                if (static::$container->hasInstance($key)) {
                    static::$services[$key][] = self::$container[$key];
                    static::$container->removeInstance($key);
                }
            }
        }

        return static::$request[] = $request;
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
                static::$modules[$module['name']] = $module + array(
                    'parameters' => array(),
                    'services' => array(),
                    'listeners' => array(),
                    'init' => null,
                );
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

}
