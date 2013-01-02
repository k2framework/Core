<?php

require_once __DIR__ . '/../autoload.php';

use K2\Kernel\Request;
use K2\Kernel\AppContext;

class AppContextTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var type 
     */
    protected $prod = false;

    /**
     *
     * @var type 
     */
    protected $modules = array(
        'KumbiaPHP' => '/../../vendor/kumbiaphp/kumbiaphp/src/',
        'Index' => '/modules/',
        'K2/Backend' => '/../../vendor/',
        'K2/Debug' => '/../../vendor/',
        'K2/Mail' => '/../../vendor/',
    );

    /**
     *
     * @var type 
     */
    protected $routes = array(
        '/' => 'Index',
        '/admin' => 'K2/Backend',
    );

    /**
     * @return Request 
     */
    protected function createRequest()
    {
        $request = new Request('http://localhost/k2/');
        $request->query->set('_url', 'index/hola_mundo');

        return $request;
    }

    public function testInitial()
    {
        $request = $this->createRequest();

        $app = new AppContext($this->prod, __DIR__
                        , $this->modules, $this->routes);

        $app->setRequest($request);

        $this->assertEquals($this->prod, $app->InProduction());
        $this->assertEquals(__DIR__, $app->getAppPath());
        $this->assertEquals($request->getBaseUrl(), $app->getBaseUrl());
        $this->assertEquals($request->getRequestUrl(), $app->getRequestUrl());
    }

    public function testRoutes()
    {
        $request = $this->createRequest();

        $app = new AppContext($this->prod, __DIR__
                        , $this->modules, $this->routes);

        $app->setRequest($request);

        $app->setRequest($request);

        $app->setCurrentModule('K2/Backend')
                ->setCurrentModuleUrl('/admin')
                ->setCurrentController('usuarios')
                ->setCurrentAction('index')
                ->setCurrentParameters();

        $this->assertEquals('K2/Backend', $app->getCurrentModule());
        $this->assertEquals('usuarios', $app->getCurrentController());
        $this->assertEquals('index', $app->getCurrentAction());
        $this->assertEquals(array(), $app->getCurrentParameters());

        $backendPath = $this->modules['K2/Backend'] . 'K2/Backend/';

        $this->assertEquals($backendPath, $app->getPath($app->getCurrentModule()));

        $this->assertEquals('http://localhost/k2/admin/usuarios', $app->getControllerUrl());
        $this->assertEquals('http://localhost/k2/admin/usuarios/index', $app->getCurrentUrl());
        $this->assertEquals('http://localhost/k2/admin/usuarios/index', $app->getCurrentUrl(true));

        $app->setCurrentModule('K2/Backend')
                ->setCurrentModuleUrl('/admin')
                ->setCurrentController('usuarios')
                ->setCurrentAction('edit')
                ->setCurrentParameters(array(4));

        $this->assertEquals('http://localhost/k2/admin/usuarios', $app->getControllerUrl());
        $this->assertEquals('http://localhost/k2/admin/usuarios/edit', $app->getCurrentUrl());
        $this->assertEquals('http://localhost/k2/admin/usuarios/edit/4', $app->getCurrentUrl(true));

        $this->assertEquals('K2/Backend', $app->getCurrentModule());
        $this->assertEquals('usuarios', $app->getCurrentController());
        $this->assertEquals('edit', $app->getCurrentAction());
        $this->assertEquals(array(4), $app->getCurrentParameters());

        $app->setCurrentModule('K2/Backend')
                ->setCurrentModuleUrl('/admin')
                ->setCurrentController('usuarios')
                ->setCurrentAction('edit')
                ->setCurrentParameters(array(4, 10));

        $this->assertEquals('http://localhost/k2/admin/usuarios', $app->getControllerUrl());
        $this->assertEquals('http://localhost/k2/admin/usuarios/edit', $app->getCurrentUrl());
        $this->assertEquals('http://localhost/k2/admin/usuarios/edit/4/10', $app->getCurrentUrl(true));

        $this->assertEquals('K2/Backend', $app->getCurrentModule());
        $this->assertEquals('usuarios', $app->getCurrentController());
        $this->assertEquals('edit', $app->getCurrentAction());
        $this->assertEquals(array(4, 10), $app->getCurrentParameters());
    }

}
