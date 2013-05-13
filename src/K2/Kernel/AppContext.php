<?php

namespace K2\Kernel;

use K2\Kernel\Request;
use K2\Kernel\Exception\NotFoundException;

/**
 * Clase que contiene la info del contexto en el que se encuentra la aplicación
 * actualmente.
 *
 * @author maguirre
 */
class AppContext
{

    /**
     * Ruta hacia el directorio app del proyecto
     * @var string 
     */
    protected $appPath;

    /**
     * Ruta hacia el directorio modules del poryecto
     * @var string 
     */
    protected $modulesPath;

    /**
     * Areglo con las nombres y directorios de los modulos del proyecto
     * @var array 
     */
    protected $modules;

    /**
     * Arreglo con los prefijos de rutas de los modulos del proyecto
     * @var array 
     */
    protected $routes;

    /**
     * Contiene el prefijo actual que representa a un modulo en el proyecto
     * @var Module 
     */
    protected $currentModule;

    /**
     * Contiene el prefijo actual que representa a un modulo en el proyecto
     * @var string 
     */
    protected $currentModuleUrl;

    /**
     * Contiene el nombre del controlador actual ejecutandose en el proyecto
     * @var string 
     */
    protected $currentController;

    /**
     * Contiene el nombre de la acción actual ejecutandose en el proyecto
     * @var string 
     */
    protected $currentAction;

    /**
     * Contiene los parametros de la petición.
     * @var array 
     */
    protected $currentParameters;

    /**
     * indica si el proyecto está en producción ó no.
     * @var boolean 
     */
    protected $inProduction;

    /**
     * Mantiene el tipo de request establecido en el Kernel (MASTER, SUB)
     * @var string 
     */
    protected $requestType;

    /**
     *
     * @var Request 
     */
    protected $request;

    /**
     * Constructor de la clase
     * @param type $inProduction
     * @param type $appPath
     * @param type $modules
     * @param type $namespaces 
     */
    public function __construct($inProduction, $appPath, $modules, $routes)
    {
        $this->inProduction = $inProduction;
        $this->appPath = $appPath;
        $this->modulesPath = rtrim($appPath, '/') . '/modules/';
        $this->modules = $modules;
        $this->routes = $routes;
    }

    /**
     * Devuelve el prefijo actual del modulo que se está ejecutando
     * @return Module 
     */
    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    /**
     * Establece el módulo actual en ejecucion
     * @param Module $currentModule 
     * @return AppContext
     */
    public function setCurrentModule(Module $currentModule)
    {
        $this->currentModule = $currentModule;
        return $this;
    }

    /**
     * Devuelve el nombre del controlador actual en ejecución
     * @return string 
     */
    public function getCurrentController()
    {
        return $this->currentController;
    }

    /**
     * Establece el nombre del controlador (en small_case) actual en ejecución
     * @param string $currentController 
     * @return AppContext
     */
    public function setCurrentController($currentController)
    {
        $this->currentController = $currentController;
        return $this;
    }

    /**
     * Devuelve el nombre de la accion actual (en small_case) en ejecución
     * @return string 
     */
    public function getCurrentAction()
    {
        return $this->currentAction;
    }

    /**
     * Establece el nombre de la accion actual en ejecución
     * @param string $currentController
     * @return AppContext
     */
    public function setCurrentAction($currentAction)
    {
        $this->currentAction = $currentAction;
        return $this;
    }

    /**
     * Devuelve los parametros de la petición.
     * @return array 
     */
    public function getCurrentParameters()
    {
        return $this->currentParameters;
    }

    /**
     * Establece los parametros de la petición, enviados por la url
     * @param array $currentParameters
     * @return AppContext 
     */
    public function setCurrentParameters(array $currentParameters = array())
    {
        $this->currentParameters = $currentParameters;
        return $this;
    }

    /**
     * Devuelve la Url actual, completa, con módulo/controlador/acción
     * así estos no hayan sido especificados en la URL.
     * @param boolean $parameters si es true, agrega los parametros de la patición.
     * @return string 
     */
    public function getCurrentUrl($parameters = FALSE)
    {
        $url = $this->createUrl("{$this->currentModule->getName()}:{$this->currentController}/{$this->currentAction}");
        if ($parameters && count($this->currentParameters)) {
            $url .= '/' . join('/', $this->currentParameters);
        }
        return $url;
    }

    /**
     * Devuelve la ruta hasta el controlador actual ejecutandose.
     * @param string $action si se especifica se añade al final de la URL
     * @return string 
     */
    public function getControllerUrl($action = null)
    {
        return rtrim($this->createUrl("{$this->currentModule->getName()}:{$this->currentController}/{$action}"), '/');
    }

    /**
     * Devuulve el prefijo de la ruta que apunta al modulo actual.
     * @return string 
     */
    public function getCurrentModuleUrl()
    {
        return $this->currentModuleUrl;
    }

    /**
     * Establece el prefijo de la url que identifica al modulo de la petición.
     * @param string $currentModuleUrl 
     * @return AppContext
     */
    public function setCurrentModuleUrl($currentModuleUrl)
    {
        $this->currentModuleUrl = $currentModuleUrl;
        return $this;
    }

}

