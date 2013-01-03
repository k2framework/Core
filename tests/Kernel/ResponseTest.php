<?php

require_once __DIR__ . '/../autoload.php';

use K2\Kernel\Response;
use K2\Kernel\JsonResponse;

class ResponseTest extends PHPUnit_Framework_TestCase
{

    public function testBasic()
    {
        $response = new Response();

        $this->assertEquals('', $response->getContent());
        $this->assertEquals('', $response->getCharset());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), $response->getCacheInfo());
        $this->assertEquals(array(), $response->headers->all());

        $response->setContent('Hola Mundo...!!!');

        $this->assertEquals('Hola Mundo...!!!', $response->getContent());
    }

    public function testAdvanced()
    {

        $html = "<html><head></head><body>Hola Mundo...!!!</body></html>";

        $status = 201;

        $headers = array(
            'Content-Type' => 'text/html'
        );

        $response = new Response($html, $status, $headers);

        $response->setCharset('UTF-8');

        $this->assertEquals($html, $response->getContent());
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals('UTF-8', $response->getCharset());
        $this->assertEquals($headers, $response->headers->all());
        $this->assertTrue($response->headers->has('Content-Type'));
        $this->assertEquals('text/html', $response->headers->get('Content-Type'));
        $this->assertEquals('text/html', $response->headers->get('Content-Type', 'application/json'));
    }

    public function testJsonResponse()
    {
        $data = array(
            'nombre' => "Manuel José",
            'edad' => 23,
            'pais' => 'Venezuela',
            'lenguajes' => array('php', 'javascript', 'c++', 'java')
        );

        $response = new JsonResponse($data);

        $this->assertInstanceOf(get_class(new Response()), $response);
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Content-Type'));
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        unset($data['edad']);

        $response->setContent($data);
        $this->assertEquals(json_encode($data), $response->getContent());
    }

}
