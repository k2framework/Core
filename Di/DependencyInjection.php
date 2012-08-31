<?php

namespace KumbiaPHP\Di;

use KumbiaPHP\Di\DependencyInjectionInterface;
use KumbiaPHP\Di\Container\Container;
use KumbiaPHP\Di\Exception\IndexNotDefinedException;
use \ReflectionClass;

/**
 * @Service(mi_servicio,$propiedad)
 * @Service( otro, $otraPropiedad)
 *
 * @author manuel
 */
class DependencyInjection implements DependencyInjectionInterface
{

    /**
     *  @var Container
     */
    protected $container;

    /**
     * Contiene los servicios que se han ido solicitando a partir de un servicio
     * inicial que depende de otros servicios no creados aun.
     * 
     * Por cada solicitud de un servicio no creado, se debe verificar que ese
     * servicio no esté ya en la cola, porque estariamos en presencia de 
     * una dependencia circular entre servicios, donde un servicio A depende
     * de un servicio B que depende del servicio A.
     * 
     * @var array 
     */
    private $queue = array();
    private $isQueue = FALSE;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function newInstance($id, $config)
    {
        if (!isset($config['class'])) {
            throw new IndexNotDefinedException("No se Encontró el indice \"class\" en la definicón del servicio \"$id\"");
        }

        $reflection = new ReflectionClass($config['class']);

        if (isset($config['construct'])) {
            $arguments = $this->getArgumentsFromConstruct($id, $config);
        } else {
            $arguments = array();
        }

        //verificamos si ya se creó una instancia en una retrollamada del
        //metodo injectObjectIntoServicesQueue
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        $instance = $reflection->newInstanceArgs($arguments);

        //agregamos la instancia del objeto al contenedor.
        $this->container->set($id, $instance);

        $this->injectObjectIntoServicesQueue();

        if (isset($config['call'])) {
            $this->setOtherDependencies($id, $instance, $config['call']);
        }
        return $instance;
    }

    protected function getArgumentsFromConstruct($id, array $config)
    {
        $args = array();
        //lo agregamos a la cola hasta que se creen los servicios del
        //que depende
        $this->addToQueue($id, $config);

        if (is_array($config['construct'])) {
            foreach ($config['construct'] as $serviceOrParameter) {
                if ('@' === $serviceOrParameter[0]) {//si comienza con @ es un servicio lo que solicita
                    $args[] = $this->container->get(substr($serviceOrParameter, 1));
                } else { //si no comienza por arroba es un parametro lo que solicita
                    $args[] = $this->container->getParameter($serviceOrParameter);
                }
            }
        } else {
            if ('@' === $config['construct'][0]) {//si comienza con @ es un servicio lo que solicita
                $args[] = $this->container->get(substr($config['construct'], 1));
            } else { //si no comienza por arroba es un parametro lo que solicita
                $args[] = $this->container->getParameter($config['construct']);
            }
        }
        //al tener los servicios que necesitamos
        //quitamos al servicio en construccion de la cola
        $this->removeToQueue($id);
        return $args;
    }

    /**
     *
     * @param type $class 
     */
    protected function setOtherDependencies($id, $object, array $calls)
    {
        foreach ($calls as $method => $serviceOrParameter) {
            if ('@' === $serviceOrParameter[0]) {//si comienza con @ es un servicio lo que solicita
                $object->$method($this->container->get(substr($serviceOrParameter, 1)));
            } else { //si no comienza por arroba es un parametro lo que solicita
                $object->$method($this->container->getParameter($serviceOrParameter));
            }
        }
    }

    /**
     * Inyecta el servicio recien creado en los servicios que lo están
     * solicitando.
     * 
     * Debe activarse el semaforo isQueue para avisar al inyector
     * de que ya existe el servicio en la cola y que no debe volver a ser
     * agregado. 
     */
    protected function injectObjectIntoServicesQueue()
    {
        $this->isQueue = TRUE;
        foreach ($this->queue as $id => $config) {
            $this->newInstance($id, $config);
        }
        $this->isQueue = FALSE;
    }

    protected function inQueue($id)
    {
        return isset($this->queue[$id]);
    }

    protected function addToQueue($id, $config)
    {
        //si el servicio actual aparece en la cola de servicios
        //indica que dicho servicio tiene una dependencia a un servicio 
        //que depende de este, por lo que hay una dependencia circular.
        if (!$this->isQueue && $this->inQueue($id)) {
            throw new \Exception("Se ha Detectado una Dependencia Circular entre Servicios");
        }
        $this->queue[$id] = $config;
    }

    protected function removeToQueue($id)
    {
        if ($this->inQueue($id)) {
            unset($this->queue[$id]);
        }
    }

}