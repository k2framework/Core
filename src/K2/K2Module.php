<?php

namespace K2;

use K2\Kernel\Module;
use K2\Kernel\Event\K2Events;

class K2Module extends Module
{

    public function init()
    {
        $this->setServices();
        $this->setListeners();

        $config = Kernel\Config\Reader::read('config');
        $config = $config['config'];

        //si se estan usando locales y ningun módulo a establecido una definición para
        //el servicio translator, lo hacemos por acá.
        if (isset($config['locales']) && !$this->container->has('translator')) {
            $this->container->set('translator', function() {
                        return new Translation\Translator();
                    });
        }

        //si se define el timezone, lo asignamos a php
        if (isset($config['timezone'])) {
            ini_set('date.timezone', $config['timezone']);
        }

        $this->container->setParameter('security', array(
            'provider' => array(
                'active_record' => 'K2\\Security\\Auth\\Provider\\ActiveRecord',
                'memory' => 'K2\\Security\\Auth\\Provider\\Memory',
            ),
        ));
        $this->container->setParameter('translator', array(
            'provider' => 'arrays'
        ));
        $this->container->setParameter('config', $config);
    }

    protected function setServices()
    {
        //acá establecemos los servicios en el container
        $this->container->setFromArray(array(
            'router' => function() {
                return new Kernel\Router\Router();
            },
            'session' => function() {
                return new Kernel\Session\Session(Kernel\App::appPath());
            },
            'view' => function($c) {
                return new View\View($c['twig']);
            },
            'twig' => function() {
                $loader = new \Twig_Loader_Filesystem(Kernel\App::appPath() . 'view');

                foreach (Kernel\App::get('app.kernel')->getModules() as $module) {
                    if (is_dir($dir = $module->getPath() . 'View/')) {
                        $loader->addPath($dir, strtr($module->getName(), '/', '_'));
                    }
                }

                $config = \K2\Kernel\App::getParameter('config');

                $twig = new \Twig_Environment($loader, array(
                    'cache' => Kernel\App::appPath() . '/temp/cache/twig/',
                    'debug' => !Kernel\App::get('app.kernel')->isProduction(),
                    'strict_variables' => true,
                    'charset' => isset($config['charset']) ? $config['charset'] : 'UTF-8',
                ));

                $twig->addExtension(new View\Twig\Extension());

                return $twig;
            },
            'cache' => function() {
                return Cache\Cache::factory(App::appPath());
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
            }
        ));
    }

    protected function setListeners()
    {
        $this->dispatcher->addListener(K2Events::REQUEST
                , array($this->container->get('router'), 'parseUrl'), 10000);
    }

}
