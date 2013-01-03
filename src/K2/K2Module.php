<?php

namespace K2;

use K2\Kernel\Module;
use K2\Kernel\Event\K2Events;

class K2Module extends Module
{

    public function init()
    {
        //acá establecemos los servicios en el container
        $this->container->set('router', function() {
                    return new Kernel\Router\Router();
                })->set('session', function() {
                    return new Kernel\Session\Session(Kernel\App::appPath());
                })->set('view', function() {
                    return new View\View();
                })->set('cache', function() {
                    return Cache\Cache::factory(App::appPath());
                })->set('flash', function($c) {
                    return new Flash\Flash($c->get('session'));
                })->set('validator', function($c) {
                    return new Validation\Validator($c);
                })->set('security', function($c) {
                    return new Security\Security($c->get('session'));
                })->set('activerecord.provider', function($c) {
                    return new Security\Auth\Provider\ActiveRecord($c);
                });

        $config = $this->container->getParameter('config');

        //si se usa el routes lo añadimos al container
        if (isset($config['routes'])) {
            $router = substr($config['routes'], 1);
            //si es el router por defecto quien reescribirá las url
            if ('router' === $router) {
                //le añadimos un listener.
                $this->dispatcher->addListener(K2Events::REQUEST, array('router', 'rewrite'), 10000);
            }
        }

        //si se estan usando locales y ningun módulo a establecido una definición para
        //el servicio translator, lo hacemos por acá.
        if (isset($config['locales']) && !$this->container->has('translator')) {
            $this->container->set('translator', function() {
                        return new Translation\Translator();
                    });
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
    }

}
