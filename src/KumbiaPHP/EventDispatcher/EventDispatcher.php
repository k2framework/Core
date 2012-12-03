<?php

namespace KumbiaPHP\EventDispatcher;

use KumbiaPHP\EventDispatcher\EventDispatcherInterface;
use KumbiaPHP\Di\Container\ContainerInterface;

/**
 * Clase que se encarga de despachar los eventos basicos del fw.
 *
 * @author manuel
 */
class EventDispatcher implements EventDispatcherInterface
{

    /**
     * Arreglo que contiene los escuchas insartados en el despachador.
     *
     * @var array 
     */
    protected $listeners = array();

    /**
     * Clase container para obtener las instancias de los servicios
     * que escuchan eventos, para llamarlos el despachar un evento.
     *
     * @var ContainerInterface 
     */
    protected $container;

    /**
     * Constructor de la clase.
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function dispatch($eventName, Event $event)
    {
        if (!$this->hasListeners($eventName)) {
            return;
        }
        foreach ($this->getListeners($eventName) as $listener) {
            $service = $this->container->get($listener[0]);
            $service->{$listener[1]}($event);
            if ($event->isPropagationStopped()) {
                return;
            }
        }
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[$eventName][$priority][] = $listener;
    }

    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]);
    }

    public function getListeners($eventName)
    {
        $this->sortListeners($eventName);
        //unimos todos los listener que estan en prioridades diferentes.
        return call_user_func_array('array_merge', $this->listeners[$eventName]);
    }

    public function removeListener($eventName, $listener)
    {
        if ($this->hasListeners($eventName)) {
            foreach ($this->listeners[$eventName] as $priority => $listeners) {
                if (false !== ($key = array_search($listener, $listeners))) {
                    unset($this->listeners[$eventName][$priority][$key]);
                    return;
                }
            }
        }
    }

    protected function sortListeners($eventName)
    {
        if (isset($this->listeners[$eventName])) {
            krsort($this->listeners[$eventName]);
        }
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $method => $params) {
            if (is_array($params)) {//si es arreglo es porque pasamos la prioridad
            } else {
                $this->addListener($eventName, $listener);
            }
        }
    }

}