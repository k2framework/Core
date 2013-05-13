<?php
require_once __DIR__ . '/../autoload.php';

use K2\Kernel\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{

    public function testInitial()
    {
        $request = new Request('/');

        $this->assertEquals('/', $request->getRequestUrl());
        $this->assertFalse($request->isAjax());

        $request = new Request('index/hola_mundo');

        $this->assertEquals('index/hola_mundo', $request->getRequestUrl());
        
        $request = new Request('/index/hola_mundo');

        $this->assertEquals('/index/hola_mundo', $request->getRequestUrl());
    }

}
