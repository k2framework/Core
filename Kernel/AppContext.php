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
    protected $moduleDir;
    protected $namespaces;
    protected $currentUrl;
    protected $modules;
    protected $currentModule;
    protected $currentController;
    protected $inProduction;

    public function __construct(Request $request, $inProduction, $appPath, $namespaces)
    {
        $this->baseUrl = $request->getBaseUrl();
        $this->inProduction = $inProduction;
        $this->appPath = $appPath;
        $this->currentUrl = $request->getRequestUrl();
        $this->moduleDir = $appPath . 'modules/';
        $this->namespaces = $namespaces;
        $this->modules = $namespaces;
        //debemos excluir el namespace del dir del core del propio fw
        unset($this->modules['KumbiaPHP']);
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

    public function getModuleDir()
    {
        return $this->moduleDir;
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    public function getModules($module = NULL)
    {
        if ($module) {
            return isset($this->modules[$module]) ? $this->modules[$module] : NULL;
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

