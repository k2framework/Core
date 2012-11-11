<?php

require_once __DIR__ . '/../../autoload.php';

use KumbiaPHP\Kernel\Session\Session;

ob_start();

class SessionTest extends PHPUnit_Framework_TestCase
{

    public function testBasic()
    {
        $session = new Session(__DIR__);
        
        $session->destroy();
        $session->start();

        $this->assertEquals(array(), $session->all());

        $this->assertNull($session->get('user_id'));

        $session->set('user_id', 203);
        $session->set('user_id', 100, 'private');

        $this->assertTrue($session->has('user_id'));
        $this->assertTrue($session->has('user_id', 'private'));

        $this->assertEquals(203, $session->get('user_id'));
        $this->assertEquals(100, $session->get('user_id', 'private'));

        $data = array(
            'default' => array(
                'user_id' => 203
            ),
            'private' => array(
                'user_id' => 100
            ),
        );
        
        $this->assertEquals($data, $session->all());
        $this->assertEquals($data['default'], $session->all('default'));
        $this->assertEquals($data['private'], $session->all('private'));

        $session->destroy();
        ob_end_flush();
    }

}
