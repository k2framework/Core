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
    }

    public static function setContext(array $data)
    {
        static::$context[static::getRequest()->getRequestUrl()] = $data;
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
            foreach ($modules as $module) {
                static::$modules[$module['name']] = $module + array(
                    'parameters' => array(),
                    'services' => array(),
                    'listeners' => array(),
                    'init' => null,
                );
            }
        }
    }

    public static function routes(array $routes = null)
    {
        if (null === $routes) {
            return static::$routes;
        } else {
            foreach ($routes as $routeName => $moduleName) {
                if (!isset(static::$modules[$moduleName])) {
                    throw new Exception\NotFoundException(sprintf('No existe el Módulo %s en la creación de la ruta %s', $moduleName, $routeName));
                }
                static::$routes[$routeName] = $moduleName;
            }
        }
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

}
