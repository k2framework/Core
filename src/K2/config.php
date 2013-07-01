<?php

namespace K2;

use K2\Kernel\App;
use K2\Kernel\Event\K2Events;
use K2\Di\Container\Container;
use K2\Kernel\Event\ExceptionEvent;
use K2\Twig\Extension\Exception as ExceptionExtension;

return array(
    'name' => 'K2Core',
    'namespace' => __NAMESPACE__,
    'path' => __DIR__,
    'services' => array(
        'router' =>  function() {
            return new Kernel\Router\Router();
        },
        'session' => function() {
            return new Kernel\Session\Session(APP_PATH);
        },
        'view' => function($c) {
            return new View\View($c['twig']);
        },
        'twig' => function(Container $c) {
            return createTwigEnviroment($c);
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
        'twig_core' => array(
            'callback' => function() {
                return new Twig\Extension\Core();
            },
            'tags' => array(
                'twig.extension' => array(),
            ),
        ),
        'twig_form' => array(
            'callback' => function($c) {
                return new Twig\Extension\Form($c->get('property_accesor'));
            },
            'tags' => array(
                'twig.extension' => array(),
            ),
        ),
        'mapper' => function($c) {
            return new Datamapper\DataMapper($c['property_accesor']);
        },
        'firewall' => function($c) {
            return new \K2\Security\Listener\Firewall($c);
        },
        'filesystem' => function($c) {
            return new \Symfony\Component\Filesystem\Filesystem();
        },
    ),
    'init' => function(Container $c) {
        if ($c->hasParameter('config.locales')) {
            $c->set("translator", function() {
                        return new Translation\Translator();
                    });
        }
        if ($c->getParameter('security.enabled')) {
            //hacemos que el firewall escuche las peticiones
            $c['event.dispatcher']
                    ->addListener(K2Events::REQUEST, array('firewall', 'onKernelRequest'), 1000);
        }

        foreach($c->getTaggedServicesConfig('event.listener') as $id => $config){
            isset($config['priority']) || $config['priority'] = 0;
            $c['event.dispatcher']
                    ->addListener($config['event'], array($id, $config['method']), $config['priority']);
        }
    }
);

function createTwigEnviroment(Container $c)
{
    $loader = new \Twig_Loader_Filesystem(APP_PATH . 'view');

    $config = (array) App::getParameter('twig');
    
    $config += array(
        'charset' => 'UTF-8',
        'cache' => APP_PATH . 'temp/cache/twig/',
        'debug' => !PRODUCTION,
        'charset' => 'UTF-8',
        'strict_variables' => true,
    );

    $twig = new \Twig_Environment($loader, $config);

    if (!PRODUCTION || App::getParameter('show_exceptions')) {
        $twig->addExtension(new \Twig_Extension_Debug());
        $twig->addExtension(new ExceptionExtension());
    }

    foreach (App::modules() as $name => $module) {
        //si existe en views una carpeta con el nombre de algun módulo
        //se agrega a los paths de twig, esto permite reescribir templates
        //en los proyectos :-)
        if (is_dir($dir = APP_PATH . 'view/' . $module['name'] . '/')) {
            $loader->addPath($dir, $name);
        }
        if (is_dir($dir = rtrim($module['path'], '/') . '/View/')) {
            $loader->addPath($dir, $name);
        }
    }

    $throw = !$c->get('app.kernel')->hasException();
    foreach ($c->getTaggedServicesConfig('twig.extension') as $id => $config) {
        //las extensiones son servicios, por lo tango los cargamos y 
        //los vamos pasando a twig
        try {
            $twig->addExtension($c->get($id));
        } catch (\Exception $e) {
            if ($throw) {//solo si no se ha lanzado una excepción la lanzamos
                throw $e;
            }
        }
    }

    return $twig;
}