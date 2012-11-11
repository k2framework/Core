<?php

require_once __DIR__ . '/../autoload.php';

use KumbiaPHP\Kernel\Request;

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

        $request->query->set('_url', 'index/hola_mundo');

        $this->assertEquals('index/hola_mundo', $request->getRequestUrl());

        $request->query->set('_url', '/index/hola_mundo');

        $this->assertEquals('/index/hola_mundo', $request->getRequestUrl());
        $this->assertEquals('/index/hola_mundo', $request->get('_url'));
        $this->assertEquals('/index/hola_mundo', $request->query->get('_url'));
    }

}
