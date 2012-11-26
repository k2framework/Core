<?php
require_once __DIR__ . '/../autoload.php';

use KumbiaPHP\Kernel\File;
use KumbiaPHP\Kernel\Collection;
use KumbiaPHP\Kernel\FilesCollection;
use KumbiaPHP\Kernel\CookiesCollection;

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
        $this->assertEquals('years', $collection->getAlpha('edad'));
    }

    public function testFilesCollection()
    {
        $_FILES = array(
            'archivo_1' => array(
                'name' => 'mi_archivo.txt',
                'tmp_name' => 'mi_archivo.txt',
                'type' => 'txt',
                'size' => 1000,
                'error' => 0,
            ),
            'archivo_2' => array(
                'name' => 'sinExtension',
                'tmp_name' => 'sinExtension',
                'type' => 'bin',
                'size' => 235,
                'error' => UPLOAD_ERR_EXTENSION,
            ),
            'ar_3' => array('error' => UPLOAD_ERR_INI_SIZE),
            'ar_4' => array('error' => UPLOAD_ERR_FORM_SIZE),
            'ar_5' => array('error' => UPLOAD_ERR_PARTIAL),
            'ar_6' => array('error' => UPLOAD_ERR_NO_FILE),
            'ar_7' => array('error' => UPLOAD_ERR_NO_TMP_DIR),
            'ar_8' => array('error' => UPLOAD_ERR_CANT_WRITE),
        );

        $file = new File($_FILES['archivo_1']);

        $collection = new FilesCollection();

        $this->assertTrue($collection->has('archivo_1'));
        $this->assertEquals($file, $collection->get('archivo_1'));

        $this->assertEquals($_FILES['archivo_1']['name'], $file->getName());
        $this->assertEquals($_FILES['archivo_1']['tmp_name'], $file->getTmpName());
        $this->assertEquals('txt', $file->getExtension());
        $this->assertEquals(1000, $file->getSize());
        $this->assertEquals(0, $file->getError());

        $file2 = $collection->get('archivo_2');

        $this->assertEquals($_FILES['archivo_2']['name'], $file2->getName());
        $this->assertEquals($_FILES['archivo_2']['tmp_name'], $file2->getTmpName());
        $this->assertNull($file2->getExtension());
        $this->assertEquals(235, $file2->getSize());
        $this->assertEquals('una extensión de php ha detenido la subida del archivo', $file2->getError());


        $this->assertEquals('el archivo excede el tamaño máximo (' . ini_get('upload_max_filesize') . 'b) permitido por el servidor', $collection->get('ar_3')->getError());
        $this->assertEquals('el archivo excede el tamaño máximo permitido', $collection->get('ar_4')->getError());
        $this->assertEquals('se ha subido el archivo parcialmente', $collection->get('ar_5')->getError());
        $this->assertEquals('no se ha subido ningún archivo', $collection->get('ar_6')->getError());
        $this->assertEquals('no se encuentra el directorio de archivos temporales', $collection->get('ar_7')->getError());
        $this->assertEquals('falló al escribir el archivo en disco', $collection->get('ar_8')->getError());
    }

}
