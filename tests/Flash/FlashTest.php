<?php

require_once __DIR__ . '/../autoload.php';

use K2\Flash\Flash;
use K2\Flash\FlashCollection;
use K2\Kernel\Session\Session;

class FlashTest extends PHPUnit_Framework_TestCase
{

    public function testFlashCollection()
    {
        $flashes = new FlashCollection();

        $this->assertEquals(array(), $flashes->all());
        $this->assertNull($flashes->get('inexistente'));

        $flashes->add('info', 'esta es una información');

        $this->assertEquals(array('esta es una información'), $flashes->get('info'));
        $this->assertNull($flashes->get('info'));

        $flashes->add('mensaje', 'Archivo Creado');
        $flashes->add('mensaje', 'Archivo Editado');

        $this->assertEquals(2, count($flashes->get('mensaje')));
        $this->assertEquals(0, count($flashes->get('mensaje')));

        $flashes->add('mensaje', 'Archivo Creado');
        $flashes->add('mensaje', 'Archivo Editado');

        $this->assertEquals(array(
            'Archivo Creado', 'Archivo Editado'
                ), $flashes->get('mensaje'));

        $flashes->add('mensaje', 'Archivo Creado');
        $flashes->add('mensaje', 'Archivo Editado');
        $flashes->add('info', 'Esta es una Info');

        $this->assertEquals(array(
            'mensaje' => array('Archivo Creado', 'Archivo Editado'),
            'info' => array('Esta es una Info'),
                ), $flashes->all());

        $this->assertEquals(array(), $flashes->all());
    }

    public function testFlash()
    {
        $session = new Session(__DIR__);

        $session->destroy();
        $session->start();

        $flash = new Flash($session);

        $this->assertEquals(array(), $flash->getAll());
        $this->assertNull($flash->get('info'));

        $flash->set('mensaje', 'Archivo Creado');

        $this->assertEquals(array('Archivo Creado'), $flash->get('mensaje'));
        $this->assertNull($flash->get('mensaje'));

        $session->destroy();
    }

}
