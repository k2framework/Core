<?php

namespace K2;

use K2\Kernel\App;
use K2\Di\Container\Container;

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

            $loader = new \Twig_Loader_Filesystem(APP_PATH . '/view');

            $config = App::getParameter('config');

            $twig = new \Twig_Environment($loader, array(
                'cache' => APP_PATH . '/temp/cache/twig/',
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
                if (is_dir($dir = APP_PATH . '/view/' . $module['name'] . '/')) {
                    $loader->addPath($dir, $name);
                }
                if (is_dir($dir = rtrim($module['path'], '/') . '/View/')) {
                    $loader->addPath($dir, $name);
                }
                //si el módulo tiene extensiones twig las agregamos a Twig_Environment
                if (isset($module['twig_extensions'])) {
                    $module['twig_extensions'] = (array) $module['twig_extensions'];
                    //las extensiones son servicios, por lo tango los cargamos y los vamos pasando
                    //a twig
                    array_walk($module['twig_extensions'], function($e)use($c, $twig) {
                                $twig->addExtension($c->get($e));
                            });
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
    ),
    'twig_extensions' => array('twig_core', 'twig_form'),
    'init' => function(Container $c) {
        $c->setParameter('security.provider', array(
            'active_record' => 'K2\\Security\\Auth\\Provider\\ActiveRecord',
            'memory' => 'K2\\Security\\Auth\\Provider\\Memory',
        ));
        $c->setParameter('translator.provider', 'arrays');
    },
);
