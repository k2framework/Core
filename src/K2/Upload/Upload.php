<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Upload
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

namespace K2\Upload;

use K2\Kernel\App;
use K2\Kernel\Request;
use K2\Upload\Adapter\File;
use K2\Upload\Adapter\Image;
use K2\Kernel\File as UploadedFile;
use K2\Upload\Exception\UploadException;

/**
 * Sube archivos al servidor.
 *
 * @category   Kumbia
 * @package    Upload
 */
abstract class Upload
{

    /**
     *
     * @var Request
     */
    protected $request;
    protected $normalized = array();

    /**
     *
     * @var array 
     */
    protected $errors = array();

    /**
     * Nombre de archivo subido por método POST
     * 
     * @var UploadedFile
     */
    protected $file;

    /**
     * Permitir subir archivos de scripts ejecutables
     *
     * @var boolean
     */
    protected $allowScripts = null;

    /**
     * Tamaño mínimo del archivo
     * 
     * @var string
     */
    protected $minSize = null;

    /**
     * Tamaño máximo del archivo
     *
     * @var string
     */
    protected $maxSize = null;

    /**
     * Tipos de archivo permitidos utilizando mime
     * 
     * @var array
     */
    protected $types = null;

    /**
     * Extensión de archivo permitida
     *
     * @var array
     */
    protected $extensions = null;

    /**
     * Permitir sobrescribir ficheros
     * 
     * @var bool Por defecto FALSE
     */
    protected $overwrite = false;

    /**
     * Ruta donde se guardara el archivo
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor
     *
     * @param string|array $name nombre de archivo por método POST
     * Si es un array, el primer valor es el form que contiene el file 
     * y el segundo valor el indice.
     */
    public function __construct(Request $request, $name)
    {
        $this->request = $request;

        $names = explode('.', $name);

        $name = '[' . join('][', $names) . ']';

        $file = App::get('property_accesor')->getValue($this->normalize($names[0]), $name);

        if (!$file) {
            throw new UploadException("No existe el archivo \"$name\" en los archivos subidos");
        }
        $this->file = new UploadedFile($file);
    }

    /**
     * Obtiene el adaptador para Upload
     *
     * @param string|array $name nombre de archivo recibido por POST
     * Si es un array, eñ ´primer valor es el form que contiene el file 
     * y el segundo valor el indice.
     * 
     * @param string $adapter (File, Image)
     * @return File|Image
     */
    public static function factory($name, $adapter = 'File')
    {
        if (!in_array($adapter, array('File', 'Image'))) {
            throw new UploadException("No se reconoce el adapter $adapter");
        }

        $class = "K2\\Upload\\Adapter\\$adapter";

        return new $class(App::getRequest(), $name);
    }

    /**
     * 
     * @return UploadedFile 
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getErrors()
    {
        return array_merge($this->errors, (array) $this->file->getError());
    }

    /**
     * Indica si se permitirá guardar archivos de scripts ejecutables
     *
     * @param boolean $value
     */
    public function setAllowScripts($value)
    {
        $this->allowScripts = $value;
    }

    /**
     * Asigna el tamaño mínimo permitido para el archivo
     *
     * @param string $size
     */
    public function setMinSize($size)
    {
        $this->minSize = trim($size);
    }

    /**
     * Asigna el tamaño máximo permitido para el archivo
     *
     * @param string $size
     */
    public function setMaxSize($size)
    {
        $this->maxSize = trim($size);
    }

    /**
     * Asigna los tipos de archivos permitido (mime)
     *
     * @param array|string $value lista de tipos de archivos permitidos (mime) si es string separado por |
     */
    public function setTypes($value)
    {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }
        $this->types = $value;
    }

    /**
     * Asigna las extensiones de archivos permitidas
     *
     * @param array|string $value lista de extensiones para archivos, si es string separado por |
     */
    public function setExtensions($value)
    {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }
        $this->extensions = $value;
    }

    /**
     * Permitir sobrescribir el fichero
     *
     * @param bool $value
     */
    public function allowOverwrite($value)
    {
        $this->overwrite = (bool) $value;
    }

    /**
     * Asigna la ruta al directorio de destino para el archivo
     * 
     * @param string $path ruta al directorio de destino (Ej: /home/usuario/data)
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Guardar el archivo en el servidor
     * 
     * @param string $name nombre con el que se guardará el archivo
     * @return boolean
     */
    protected function saveFile($name)
    {
        return $this->file->move($this->path, $name, true);
    }

    /**
     * Guarda el archivo subido
     *
     * @param string $name nombre con el que se guardara el archivo
     * @return UploadedFile
     */
    public function save($name = null)
    {
        if ($this->file->hasError()) {
            return false;
        }
        if (null === $name) {
            $name = $this->file->getName();
        }
        // Guarda el archivo
        if ($this->overwrite($name) && $this->validates() && $this->saveFile($name)) {
            $this->file->setName($name);
            return $this->file;
        }
    }

    /**
     * Guarda el archivo con un nombre aleatorio
     * 
     * @return string|boolean Nombre de archivo generado o FALSE si falla
     */
    public function saveRandom()
    {
        // Genera el nombre de archivo
        $name = md5(time());

        // Guarda el archivo
        return $this->save($name);
    }

    /**
     * Valida el archivo antes de guardar
     * 
     * @return boolean
     */
    protected function validates()
    {
        // Verifica que se pueda escribir en el directorio
        if (!is_writable($this->path)) {
            $this->errors[] = 'Error: no se puede escribir en el directorio';
            return false;
        }
        // Denegar subir archivos de scripts ejecutables
        if (!$this->allowScripts && preg_match('/\.(php|phtml|php3|php4|js|shtml|pl|py|rb|rhtml)$/i', $this->file->getName())) {
            $this->errors[] = 'Error: no esta permitido subir scripts ejecutables';
            return false;
        }

        // Valida el tipo de archivo
        if ($this->types !== NULL && !$this->validatesTypes()) {
            $this->errors[] = 'Error: el tipo de archivo no es válido';
            return false;
        }

        // Valida extensión del archivo
        if ($this->extensions !== NULL && !preg_match('/\.(' . implode('|', $this->extensions) . ')$/i', $this->file->getName())) {
            $this->errors[] = 'Error: la extensión del archivo no es válida';
            return false;
        }

        // Verifica si es superior al tamaño indicado
        if ($this->maxSize !== NULL && $this->file['size'] > $this->toBytes($this->maxSize)) {
            $this->errors[] = "Error: no se admiten archivos superiores a $this->maxSize" . 'b';
            return false;
        }

        // Verifica si es inferior al tamaño indicado
        if ($this->minSize !== NULL && $this->file['size'] < $this->toBytes($this->minSize)) {
            $this->errors[] = "Error: no se admiten archivos inferiores a $this->minSize" . 'b';
            return false;
        }

        return true;
    }

    /**
     * Valida que el tipo de archivo
     *
     * @return boolean
     */
    protected function validatesTypes()
    {
        return in_array($this->file->getType(), $this->types);
    }

    /**
     * Valida si puede sobrescribir el archivo
     *
     * @return boolean
     */
    protected function overwrite($name)
    {
        if ($this->overwrite) {
            return true;
        }
        if (is_file("$this->path/$name")) {
            $this->errors[] = 'Error: ya existe este fichero. Y no se permite reescribirlo';
            return false;
        }
        return true;
    }

    /**
     * Convierte de tamaño legible por humanos a bytes
     *
     * @param string $size
     * @return int
     */
    protected function toBytes($size)
    {
        if (is_int($size) || ctype_digit($size)) {
            return (int) $size;
        }

        $tipo = strtolower(substr($size, -1));
        $size = (int) $size;

        switch ($tipo) {
            case 'g': //Gigabytes
                $size *= 1073741824;
                break;
            case 'm': //Megabytes
                $size *= 1048576;
                break;
            case 'k': //Kilobytes
                $size *= 1024;
                break;
            default :
                $size = -1;
                $this->errors[] = 'Error: el tamaño debe ser un int para bytes, o un string terminado con K, M o G. Ej: 30k , 2M, 2G';
        }

        return $size;
    }

    protected function normalize($index)
    {
        if (!isset($_FILES[$index])) {
            return array();
        }

        if (isset($this->normalized[$index])) {
            return $this->normalized[$index];
        }

        $data = $_FILES[$index];

        if (isset($data['name']) && is_array($data['name'])) {
            $files = array();
            foreach ($data['name'] as $k => $name) {
                $files[$k] = array(
                    'name' => $name,
                    'tmp_name' => $data['tmp_name'][$k],
                    'size' => $data['size'][$k],
                    'type' => $data['type'][$k],
                    'error' => $data['error'][$k]
                );
            }
            return $this->normalized[$index] = array($index => $files);
        }

        return $_FILES;
    }

}
