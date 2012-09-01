<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Kernel\Request;

/**
 * Description of RouteContext
 *
 * @author maguirre
 */
class AppContext
{

    protected $baseUrl;
    protected $appPath;
    protected $modulesPath;
    protected $routes;
    protected $currentUrl;
    protected $modules;
    protected $namespaces;
    protected $currentModule;
    protected $currentController;
    protected $inProduction;

    public function __construct(Request $request, $inProduction, $appPath, $modules, $namespaces)
    {
        $this->baseUrl = $request->getBaseUrl();
        $this->inProduction = $inProduction;
        $this->appPath = $appPath;
        $this->currentUrl = $request->getRequestUrl();
        $this->modulesPath = $appPath . 'modules/';
        $this->modules = $modules;
        $this->namespaces = $namespaces;
    }

    public function setRequest(Request $request)
    {
        $this->currentUrl = $request->getRequestUrl();
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getAppPath()
    {
        return $this->appPath;
    }

    public function getCurrentUrl()
    {
        return $this->currentUrl;
    }

    public function getModulesPath()
    {
        return $this->modulesPath;
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    public function getModules($route = NULL)
    {
        if ($route) {
            return isset($this->modules[$route]) ? $this->modules[$route] : NULL;
        } else {
            return $this->modules;
        }
    }

    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    public function setCurrentModule($currentModule)
    {
        $this->currentModule = $currentModule;
    }

    public function getCurrentController()
    {
        return $this->currentController;
    }

    public function setCurrentController($currentController)
    {
        $this->currentController = $currentController;
    }

    public function InProduction()
    {
        return $this->inProduction;
    }
}

