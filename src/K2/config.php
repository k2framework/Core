<?php

namespace K2;

use K2\Kernel\App;
use K2\Kernel\Event\K2Events;
use K2\Di\Container\Container;
use K2\Kernel\Event\ExceptionEvent;
use K2\Kernel\Exception\ExceptionHandler;

return array(
    'name' => 'K2Core',
    'namespace' => __NAMESPACE__,
    'path' => __DIR__,
    'services' => array(
        'router' => function() {
            return new Kernel\Router\Router();
        },
        'session' => function() {
            return new Kernel\Session\Session(APP_PATH);
        },
        'view' => function($c) {
            return new View\View($c['twig']);
        },
        'twig' => function(Container $c) {
            App::addSerciveToRequest('twig');

            $loaderFiles = new \Twig_Loader_Filesystem(APP_PATH . 'view');
            $loaderString = new \Twig_Loader_String();
            $loader = new \Twig_Loader_Chain(array($loaderFiles, $loaderString));

            $config = App::getParameter('config');

            $twig = new \Twig_Environment($loader, array(
                'cache' => APP_PATH . 'temp/cache/twig/',
                'debug' => !PRODUCTION,
                'strict_variables' => true,
                'charset' => isset($config['charset']) ? $config['charset'] : 'UTF-8',
            ));

            if (!PRODUCTION) {
                $twig->addExtension(new \Twig_Extension_Debug());
            }

            foreach (App::modules() as $name => $module) {
                //si existe en views una carpeta con el nombre de algun módulo
                //se agrega a los paths de twig, esto permite reescribir templates
                //en los proyectos :-)
                if (is_dir($dir = APP_PATH . 'view/' . $module['name'] . '/')) {
                    $loaderFiles->addPath($dir, $name);
                }
                if (is_dir($dir = rtrim($module['path'], '/') . '/View/')) {
                    $loaderFiles->addPath($dir, $name);
                }
                //si el módulo tiene extensiones twig las agregamos a Twig_Environment
                if (isset($module['twig_extensions'])) {
                    //las extensiones son servicios, por lo tango los cargamos y los vamos pasando
                    //a twig
                    foreach ((array) $module['twig_extensions'] as $ext) {
                        $throw = !$c->get('app.kernel')->hasException();
                        try {
                            $twig->addExtension($c->get($ext));
                        } catch (\Exception $e) {
                            if ($throw) {//solo si no se ha lanzado una excepción la lanzamos
                                throw $e;
                            }
                        }
                    }
                }
            }

            if (!PRODUCTION) {
                $twig->addExtension(new \Twig_Extension_Debug());
            }

            //registramos un callback para cuando no se encuentre una funcion twig, busque primero
            //si es una funcion de php y así no tire una excepción
            $twig->registerUndefinedFunctionCallback(function($name) {
                        if (function_exists($name)) {
                            return new \Twig_Function_Function($name);
                        }
                        return false;
                    });

            return $twig;
        },
        'cache' => function() {
            return Cache\Cache::factory(APP_PATH);
        },
        'flash' => function($c) {
            return new Flash\Flash($c['session']);
        },
        'validator' => function($c) {
            return new Validation\Validator($c);
        },
        'security' => function($c) {
            return new Security\Security($c['session']);
        },
        'activerecord.provider' => function($c) {
            return new Security\Auth\Provider\ActiveRecord($c);
        },
        'twig_core' => function() {
            return new View\Twig\Extension\Core();
        },
        'twig_form' => function() {
            return new View\Twig\Extension\Form();
        },
        'mapper' => function($c) {
            return new Datamapper\DataMapper($c['property_accesor']);
        },
    ),
    'twig_extensions' => array('twig_core', 'twig_form'),
    'init' => function(Container $c) {
        $c->setParameter('security.provider', array(
            'active_record' => 'K2\\Security\\Auth\\Provider\\ActiveRecord',
            'memory' => 'K2\\Security\\Auth\\Provider\\Memory',
        ));
        $c->setParameter('translator.provider', 'arrays');
    },
    'listeners' => array(
        K2Events::EXCEPTION => array(
            //si ningun evento crea una respuesta para la excepción, retornamos
            //la excepción del fw
            -1 => function(ExceptionEvent $e) {
                $e->setResponse(ExceptionHandler::createException($e->getException()));
            },
        )
    ),
);
