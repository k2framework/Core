<?php
require_once __DIR__ . '/../autoload.php';

use K2\Kernel\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{

    public function testInitial()
    {
        $request = new Request();

        $this->assertEquals('/', $request->getRequestUrl());
        $this->assertNull($request->getBaseUrl());
        $this->assertNull($request->getAppContext());
        $this->assertFalse($request->isAjax());

        $request = new Request('http://localhost/k2/');

        $this->assertEquals('/', $request->getRequestUrl());
        $this->assertEquals('http://localhost/k2/', $request->getBaseUrl());

        $_GET['_url'] = 'index/hola_mundo';

        $this->assertEquals('index/hola_mundo', $request->getRequestUrl());
        
        $_GET['_url'] = '/index/hola_mundo';

        $this->assertEquals('/index/hola_mundo', $request->getRequestUrl());
    }

}
