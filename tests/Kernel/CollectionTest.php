<?php

require_once 'autoload.php';

use KumbiaPHP\Kernel\Collection;

class CollectionTest extends PHPUnit_Framework_TestCase
{

    protected function params()
    {
        return array(
            'nombre' => "Manuel",
            'apellido' => "Aguirre",
            'pais' => "Venezuela",
        );
    }

    public function testCollectionEmpty()
    {
        $collection = new Collection();

        $this->assertEquals(array(), $collection->all());

        $this->assertFalse($collection->has('edad'));
        $this->assertEmpty($collection->get('edad'));
        $this->assertEquals('Manuel', $collection->get('nombre', 'Manuel'));
        $this->assertEquals(0, $collection->count());
        $this->assertEquals(array(), $collection->keys());
        $this->assertEquals('a:0:{}', $collection->serialize());
    }

    public function testWithData()
    {
        $collection = new Collection($this->params());

        $this->assertNotEquals(array(), $collection->all());
        $this->assertEquals($this->params(), $collection->all());

        $this->assertTrue($collection->has('pais'));
        $this->assertEmpty($collection->get('edad'));
        $this->assertEquals('Manuel', $collection->get('nombre'));
        $this->assertEquals('Manuel', $collection->get('nombre', 'Carlos'));
        $this->assertEquals(3, $collection->count());
        $this->assertEquals(array_keys($this->params()), $collection->keys());
        $this->assertEquals(serialize($this->params()), $collection->serialize());
    }

    public function testChangeData()
    {
        $collection = new Collection($this->params());

        $this->assertArrayHasKey('nombre', $collection->all());

        $this->assertEquals('Venezuela', $collection->get('pais'));
        $collection->set('pais', 'Colombia');
        $this->assertEquals('Colombia', $collection->get('pais'));

        $collection->delete('nombre');

        $this->assertEquals(2, $collection->count());

        $this->assertEquals('Carlos', $collection->get('nombre', 'Carlos'));

        $this->assertFalse($collection->has('nombre'));

        $collection->clear();

        $this->assertEquals(array(), $collection->all());

        $this->assertEquals(array(), $collection->keys());
    }

    public function testFilters()
    {
        $collection = new Collection($this->params());

        $this->assertEquals('Manuel', $collection->getAlpha('nombre'));
        $this->assertEmpty($collection->getDigits('nombre'));
        $this->assertEmpty($collection->getInt('nombre'));
        //pendiente con getAlnum, no funciona bien por el momento.
        //$this->assertEquals('Aguirre',$collection->getAlnum('apellido'));
        $this->assertEquals('Aguirre', $collection->getAlpha('apellido'));
        
        $collection->set('edad', '24');
        
        $this->assertEquals(24, $collection->getInt('edad'));
        $this->assertEquals(24, $collection->getDigits('edad'));
        $this->assertEmpty($collection->getAlpha('edad'));
        
        $collection->set('edad', '24 years');
        
        $this->assertEquals(24, $collection->getInt('edad'));
        $this->assertEquals(24, $collection->getDigits('edad'));
        $this->assertEquals('years',$collection->getAlpha('edad'));
        
        
    }

}
