<?php

namespace K2;

use K2\Kernel\App;
use K2\Di\Container\Container;
use K2\Kernel\Event\K2Events;

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
        'twig' => function() {
            $loader = new \Twig_Loader_Filesystem(APP_PATH . '/view');

            foreach (App::modules() as $name => $module) {
                if (is_dir($dir = rtrim($module['path'], '/') . '/View/')) {
                    $loader->addPath($dir, $name);
                }
            }

            $config = App::getParameter('config');

            $twig = new \Twig_Environment($loader, array(
                'cache' => APP_PATH . '/temp/cache/twig/',
                'debug' => !PRODUCTION,
                'strict_variables' => true,
                'charset' => isset($config['charset']) ? $config['charset'] : 'UTF-8',
            ));

            $twig->addExtension(new View\Twig\Extension\Core());
            if (!PRODUCTION) {
                $twig->addExtension(new \Twig_Extension_Debug());
            }
            $twig->addExtension(new View\Twig\Extension\Form());

            return $twig;
        },
        'cache' => function() {
            return Cache\Cache::factory(\Kernel\App::appPath());
        },
        'flash' => function($c) {
            return new Flash\Flash($c->get('session'));
        },
        'validator' => function($c) {
            return new Validation\Validator($c);
        },
        'security' => function($c) {
            return new Security\Security($c->get('session'));
        },
        'activerecord.provider' => function($c) {
            return new Security\Auth\Provider\ActiveRecord($c);
        },
        'property_accesor' => function($c) {
            return new \Symfony\Component\PropertyAccess\PropertyAccessor();
        },
        'mapper' => function($c) {
            return new Datamapper\DataMapper($c['property_accesor']);
        }
    ),
    'parameters' => array(),
    'listeners' => array(
        K2Events::REQUEST => array(
            10000 => array('router', 'parseUrl'),
        ),
    ),
    'init' => function(Container $c) {
        $c->setParameter('security', array(
            'provider' => array(
                'active_record' => 'K2\\Security\\Auth\\Provider\\ActiveRecord',
                'memory' => 'K2\\Security\\Auth\\Provider\\Memory',
            ),
        ));
        $c->setParameter('translator', array(
            'provider' => 'arrays'
        ));
    },
);
