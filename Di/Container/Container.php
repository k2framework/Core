<?php

namespace KumbiaPHP\Di\Container;

use KumbiaPHP\Di\Container\ContainerInterface;
use KumbiaPHP\Di\DependencyInjectionInterface as Di;
use KumbiaPHP\Di\Definition\DefinitionManager;
use KumbiaPHP\Di\Container\Services;
use KumbiaPHP\Di\Definition\Service;

/**
 * Description of Container
 *
 * @author manuel
 */
class Container implements ContainerInterface
{

    /**
     * 
     * @var Services
     */
    protected $services;

    /**
     *
     * @var Di 
     */
    protected $di;

    /**
     *
     * @var DefinitionManager 
     */
    protected $definitioManager;

    public function __construct(Di $di, DefinitionManager $dm = NULL)
    {
        $this->services = new Services();
        $this->di = $di;
        $this->definitioManager = $dm ? : new DefinitionManager();

        $di->setContainer($this);

        //agregamos al container como servicio.
        $this->set('container', $this);
    }

    public function get($id)
    {

        if ($this->services->has($id)) {
            //si existe el servicio lo devolvemos
            return $this->services->get($id);
        }
        //si no existe debemos crearlo
        //buscamos el servicio en el contenedor de servicios
        if (!$this->definitioManager->hasService($id)) {
            throw new \Exception(sprintf('No existe el servicio <b>%s</b>', $id));
        }

        $config = $this->definitioManager->getService($id)->getConfig();

        //retorna la instancia recien creada
        return $this->di->newInstance($id, $config);
    }

    public function has($id)
    {
        return $this->services->has($id);
    }

    public function set($id, $object)
    {
        $this->services->replace($id, $object);
        //y lo agregamos a las definiciones. (solo será a gregado si no existe)
        $this->definitioManager->addService(new Service($id, array(
                    'class' => get_class($object)
                )));
    }

    public function getParameter($id)
    {
        if ($this->hasParameter($id)) {
            return $this->definitioManager->getParam($id)->getValue();
        } else {
            return NULL;
        }
    }

    public function hasParameter($id)
    {
        return $this->definitioManager->hasParam($id);
    }

    public function getDefinitionManager()
    {
        return $this->definitioManager;
    }

}