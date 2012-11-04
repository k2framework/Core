<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Kernel\Request;

/**
 * Clase que contiene la info del contexto en el que se encuentra la aplicación
 * actualmente.
 *
 * @author maguirre
 */
class AppContext
{

    /**
     * Url base del proyecto
     * @var string 
     */
    protected $baseUrl;

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
     * Contiene la url actual de la petición
     * @var string 
     */
    protected $currentUrl;

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
     * @var string 
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
     * indica si el proyecto está en producción ó no.
     * @var boolean 
     */
    protected $inProduction;

    /**
     * Constructor de la clase
     * @param Request $request
     * @param type $inProduction
     * @param type $appPath
     * @param type $modules
     * @param type $namespaces 
     */
    public function __construct(Request $request, $inProduction, $appPath, $modules, $routes)
    {
        $this->baseUrl = $request->getBaseUrl();
        $this->inProduction = $inProduction;
        $this->appPath = $appPath;
        $this->currentUrl = $request->getRequestUrl();
        $this->modulesPath = $appPath . 'modules/';
        $this->modules = $modules;
        $this->routes = $routes;
    }

    /**
     * Establece la nueva url cuando se hace un forward.
     * @param Request $request 
     */
    public function setRequest(Request $request)
    {
        $this->currentUrl = $request->getRequestUrl();
    }

    /**
     * Devuelve la url base del proyecto
     * @return string 
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Devuelve la ruta hacia la carpeta app
     * @return string 
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    /**
     * devuelve la url actual de la petición
     * @return string 
     */
    public function getCurrentUrl()
    {
        return $this->currentUrl;
    }

    /**
     * Devuelve la ruta hacia la carpeta de los modulos de la app
     * @return string 
     */
    public function getModulesPath()
    {
        return $this->modulesPath;
    }

    /**
     * Devuelve la ruta hacia la carpeta de los modulos de la app
     * @return string 
     */
    public function getPath($module)
    {
        if (isset($this->modules[$module])) {
            return rtrim($this->modules[$module], '/') . "/{$module}/";
        } else {
            return NULL;
        }
    }

    /**
     * devuelve los modulos registrados en el proyecto
     * @return array 
     */
    public function getModules($module = NULL)
    {
        if ($module) {
            return isset($this->modules[$module]) ? $this->modules[$module] : NULL;
        } else {
            return $this->modules;
        }
    }

    /**
     * devuelve las rutas registrados en el proyecto
     * @param string $route si se suministra un prefijo, devuelve solo
     * el valor de la ruta para ese prefijo.
     * @return array|string|NULL 
     */
    public function getRoutes($route = NULL)
    {
        if ($route) {
            if (isset($this->routes[$route])) {
                return isset($this->modules[$this->routes[$route]]) ? $this->routes[$route] : NULL;
            } else {
                return NULL;
            }
        } else {
            return $this->routes;
        }
    }

    /**
     * Devuelve el prefijo actual del modulo que se está ejecutando
     * @return string 
     */
    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    /**
     * Establece el modulo actual en ejecucion
     * @param string $currentModule 
     */
    public function setCurrentModule($currentModule)
    {
        $this->currentModule = $currentModule;
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
     * Establece el nombre del controlador actual en ejecución
     * @param string $currentController 
     */
    public function setCurrentController($currentController)
    {
        $this->currentController = $currentController;
    }

    /**
     * Devuelve el nombre de la accion actual en ejecución
     * @return string 
     */
    public function getCurrentAction()
    {
        return $this->currentAction;
    }

    /**
     * Establece el nombre de la accion actual en ejecución
     * @param string $currentController
     */
    public function setCurrentAction($currentAction)
    {
        $this->currentAction = $currentAction;
    }

    public function createUrl($parameters = FALSE)
    {
        if ('/' !== $this->currentModuleUrl) {
            $url = $this->currentModuleUrl . '/' . $this->currentController .
                    '/' . $this->currentAction;
        } else {
            $url = $this->currentController . '/' . $this->currentAction;
        }

        if ($parameters) {
            $url .= substr($this->currentUrl, strlen($url) + 1);
        }

        return trim($url, '/') . '/';
    }

    /**
     * devuelve TRUE si la app se encuentra en producción.
     * @return boolean 
     */
    public function InProduction()
    {
        return $this->inProduction;
    }

    public function getControllerUrl()
    {
        return $this->getBaseUrl() . trim($this->currentModuleUrl, '/') . '/' . $this->currentController;
    }

    public function getCurrentModuleUrl()
    {
        return $this->currentModuleUrl;
    }

    public function setCurrentModuleUrl($currentModuleUrl)
    {
        $this->currentModuleUrl = $currentModuleUrl;
    }

}

