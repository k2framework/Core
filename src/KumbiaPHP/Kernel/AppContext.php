<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Exception\NotFoundException;

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
    protected $requestUrl;

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
        $this->requestUrl = $request->getRequestUrl();
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
        $this->requestUrl = $request->getRequestUrl();
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
    public function getRequestUrl()
    {
        return $this->requestUrl;
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

    /**
     * Devuelve la Url actual, completa, con módulo/controlador/acción
     * así estos no hayan sido especificados en la URL.
     * @param boolean $parameters si es true, agrega los parametros de la patición.
     * @return string 
     */
    public function getCurrentUrl($parameters = FALSE)
    {
        if ('/' !== $this->currentModuleUrl) {
            $url = $this->currentModuleUrl . '/' . $this->currentController .
                    '/' . $this->currentAction;
        } else {
            $url = $this->currentController . '/' . $this->currentAction;
        }

        if ($parameters) {
            $url .= substr($this->requestUrl, strlen($url));
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

    /**
     * Devuelve la ruta hasta el controlador actual ejecutandose.
     * @return string 
     */
    public function getControllerUrl()
    {
        return $this->getBaseUrl() . trim($this->currentModuleUrl, '/') . '/' . $this->currentController;
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
     */
    public function setCurrentModuleUrl($currentModuleUrl)
    {
        $this->currentModuleUrl = $currentModuleUrl;
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
     * @return string
     * @throws NotFoundException si no existe el módulo
     */
    public function createUrl($url)
    {
        $url = explode(':', $url);
        if (count($url) > 1) {
            if (!$route = array_search($url[0], $this->routes)) {
                throw new NotFoundException("No Existe el módulo {$url[0]}, no se pudo crear la url");
            }
            return $this->getBaseUrl() . trim($route, '/') . '/' . $url[1];
        } else {
            return $this->getBaseUrl() . ltrim($url[0], '/');
        }
    }

}

