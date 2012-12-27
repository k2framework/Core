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
     * Establece la nueva url cuando se hace un forward.
     * @param Request $request 
     * @return AppContext
     */
    public function setRequest(Request $request)
    {
        $request->setAppContext($this);
        $this->request = $request;
        return $this;
    }

    public function setLocales($locales = null)
    {
        $this->locales = explode(',', $locales);
        return $this;
    }

    /**
     * Establece el tipo de request del kernel, (MASTER, SUB)
     * @param string $type
     * @return \K2\Kernel\AppContext 
     */
    public function setRequestType($type)
    {
        $this->requestType = $type;
        return $this;
    }

    /**
     * Devuelve el tipo de request (MASTER, SUB)
     * @return string 
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * Devuelve la url base del proyecto
     * @return string 
     */
    public function getBaseUrl()
    {
        return $this->request->getBaseUrl();
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
    public function getRequestUrl()
    {
        return $this->request->getRequestUrl();
    }

    /**
     * Devuelve la ruta hacia la carpeta del módulo en cuestión.
     * @param string $module nombre del Módulo
     * @return null|string 
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
     * Establece el módulo actual en ejecucion
     * @param string $currentModule 
     * @return AppContext
     */
    public function setCurrentModule($currentModule)
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
        $url = $this->createUrl("{$this->currentModule}:{$this->currentController}/{$this->currentAction}");
        if ($parameters && count($this->currentParameters)) {
            $url .= '/' . join('/', $this->currentParameters);
        }
        return $url;
    }

    /**
     * devuelve TRUE si la app se encuentra en producción.
     * @return boolean 
     */
    public function InProduction()
    {
        return $this->inProduction;
    }

    /**
     * Devuelve la ruta hasta el controlador actual ejecutandose.
     * @param string $action si se especifica se añade al final de la URL
     * @return string 
     */
    public function getControllerUrl($action = null)
    {
        return rtrim($this->createUrl("{$this->currentModule}:{$this->currentController}/{$action}"), '/');
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

    /**
     * Crea una url válida. todos las libs y helpers la usan.
     * 
     * Ejemplos:
     * 
     * $this->createUrl('admin/usuarios/perfil');
     * $this->createUrl('admin/roles');
     * $this->createUrl('admin/recursos/editar/2');
     * $this->createUrl('K2/Backend:usuarios'); módulo:controlador/accion/params
     * 
     * El ultimo ejemplo es una forma especial de crear rutas
     * donde especificamos el nombre del módulo en vez del prefijo.
     * ya que el prefijo lo podemos cambiar a nuestro antojo.
     * 
     * @param string $url
     * @param boolean $baseUrl indica si se devuelve con el baseUrl delante ó no
     * @return string
     * @throws NotFoundException si no existe el módulo
     */
    public function createUrl($url, $baseUrl = true)
    {
        $url = explode(':', $url);
        if (count($url) > 1) {
            if (!$route = array_search($url[0], $this->routes)) {
                throw new NotFoundException("No Existe el módulo {$url[0]}, no se pudo crear la url");
            }
            $url = ltrim(trim($route, '/') . '/' . $url[1], '/');
        } else {
            $url = ltrim($url[0], '/');
        }
        //si se usa locale, lo añadimos a la url.
        $this->request->getLocale() && $url = $this->request->getLocale() . '/' . $url;
        return $baseUrl ? $this->request->getBaseUrl() . $url : $url;
    }
}

