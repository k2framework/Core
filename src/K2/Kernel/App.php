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
     * 
     * @return Request
     */
    public static function getRequest()
    {
        return self::$container->get('request');
    }

    /**
     * 
     * @return AppContext
     */
    public static function getContext()
    {
        return self::$container->get('app.context');
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
    public static function appPath()
    {
        return self::$container->getParameter('app_dir');
    }

    /**
     * 
     * @return string
     */
    public static function requestUrl()
    {
        return self::getRequest()->getRequestUrl();
    }

    /**
     * 
     * @return string
     */
    public static function baseUrl()
    {
        return self::getRequest()->getBaseUrl();
    }

}
