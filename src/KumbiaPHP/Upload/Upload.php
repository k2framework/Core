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

namespace KumbiaPHP\Upload;

use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Upload\Adapter\File;
use KumbiaPHP\Upload\Adapter\Image;
use KumbiaPHP\Upload\Exception\UploadException;

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

    /**
     *
     * @var array 
     */
    protected $errors = array();

    /**
     * Nombre de archivo subido por método POST
     * 
     * @var string
     */
    protected $file;

    /**
     * Permitir subir archivos de scripts ejecutables
     *
     * @var boolean
     */
    protected $allowScripts = FALSE;

    /**
     * Tamaño mínimo del archivo
     * 
     * @var string
     */
    protected $minSize = NULL;

    /**
     * Tamaño máximo del archivo
     *
     * @var string
     */
    protected $maxSize = NULL;

    /**
     * Tipos de archivo permitidos utilizando mime
     * 
     * @var array
     */
    protected $types = NULL;

    /**
     * Extensión de archivo permitida
     *
     * @var array
     */
    protected $extensions = NULL;

    /**
     * Permitir sobrescribir ficheros
     * 
     * @var bool Por defecto FALSE
     */
    protected $overwrite = FALSE;

    /**
     * Constructor
     *
     * @param string $name nombre de archivo por método POST
     */
    public function __construct(Request $request, $name)
    {
        $this->request = $request;
        if (!$this->file = $request->files->get($name)) {
            throw new UploadException("No existe el indice \"$name\" en los archivos subidos");
        }
    }

    /**
     * Obtiene el adaptador para Upload
     *
     * @param string $name nombre de archivo recibido por POST
     * @param string $adapter (File, Image)
     * @return File|Image
     */
    public static function factory(Request $request, $name, $adapter = 'File')
    {
        if (!in_array($adapter, array('File', 'Image'))) {
            throw new UploadException("No se reconoce el adapter $adapter");
        }

        $class = "KumbiaPHP\\Upload\\Adapter\\$adapter";

        return new $class($request, $name);
    }

    public function getErrors()
    {
        return $this->errors;
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
     * Acciones antes de guardar
     *
     * @param string $name nombre con el que se va a guardar el archivo
     * @return boolean
     */
    protected function beforeSave($name)
    {
        
    }

    /**
     * Acciones después de guardar
     * 
     * @param string $name nombre con el que se guardo el archivo
     */
    protected function afterSave($name)
    {
        
    }

    /**
     * Guarda el archivo subido
     *
     * @param string $name nombre con el que se guardara el archivo
     * @return boolean
     */
    public function save($name = NULL)
    {
        if (!$this->isUploaded()) {
            return FALSE;
        }
        if (!$name) {
            $name = $this->file['name'];
        } else {
            $name = $name . $this->getExtension();
        }
        // Guarda el archivo
        if ($this->beforeSave($name) !== FALSE && $this->overwrite($name) && $this->validates() && $this->saveFile($name)) {
            $this->afterSave($name);
            return TRUE;
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
        if ($this->save($name)) {
            return $name . $this->getExtension();
        }

        return FALSE;
    }

    /**
     * Verifica si el archivo esta subido en el servidor y listo para guardarse
     * 
     * @return boolean
     */
    public function isUploaded()
    {
        // Verifica si ha ocurrido un error al subir
        if ($this->file['error'] > 0) {
            //if ($_FILES[$this->name]['error'] > 0) {
            $error = array(
                UPLOAD_ERR_INI_SIZE => 'el archivo excede el tamaño máximo (' . ini_get('upload_max_filesize') . 'b) permitido por el servidor',
                UPLOAD_ERR_FORM_SIZE => 'el archivo excede el tamaño máximo permitido',
                UPLOAD_ERR_PARTIAL => 'se ha subido el archivo parcialmente',
                UPLOAD_ERR_NO_FILE => 'no se ha subido ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'no se encuentra el directorio de archivos temporales',
                UPLOAD_ERR_CANT_WRITE => 'falló al escribir el archivo en disco',
                UPLOAD_ERR_EXTENSION => 'una extensión de php ha detenido la subida del archivo'
            );

            $this->errors[] = 'Error: ' . $error[$this->file['error']];
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Valida el archivo antes de guardar
     * 
     * @return boolean
     */
    protected function validates()
    {
        // Denegar subir archivos de scripts ejecutables
        if (!$this->allowScripts && preg_match('/\.(php|phtml|php3|php4|js|shtml|pl|py|rb|rhtml)$/i', $this->file['name'])) {
            $this->errors[] = 'Error: no esta permitido subir scripts ejecutables';
            return FALSE;
        }

        // Valida el tipo de archivo
        if ($this->types !== NULL && !$this->validatesTypes()) {
            $this->errors[] = 'Error: el tipo de archivo no es válido';
            return FALSE;
        }

        // Valida extensión del archivo
        if ($this->extensions !== NULL && !preg_match('/\.(' . implode('|', $this->extensions) . ')$/i', $this->file['name'])) {
            $this->errors[] = 'Error: la extensión del archivo no es válida';
            return FALSE;
        }

        // Verifica si es superior al tamaño indicado
        if ($this->maxSize !== NULL && $this->file['size'] > $this->toBytes($this->maxSize)) {
            $this->errors[] = "Error: no se admiten archivos superiores a $this->maxSize" . 'b';
            return FALSE;
        }

        // Verifica si es inferior al tamaño indicado
        if ($this->minSize !== NULL && $this->file['size'] < $this->toBytes($this->minSize)) {
            $this->errors[] = "Error: no se admiten archivos inferiores a $this->minSize" . 'b';
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Valida que el tipo de archivo
     *
     * @return boolean
     */
    protected function validatesTypes()
    {
        return in_array($this->file['type'], $this->types);
    }

    /**
     * Devuelve la extensión
     *
     * @return string
     */
    protected function getExtension()
    {
        if ($ext = explode('.', $this->file['name'])) {
            $ext = '.' . end($ext);
        } else {
            $ext = NULL;
        }

        return $ext;
    }

    /**
     * Valida si puede sobrescribir el archivo
     *
     * @return boolean
     */
    protected function overwrite($name)
    {
        if ($this->overwrite) {
            return TRUE;
        }
        if (file_exists("$this->path/$name")) {
            $this->errors[] = 'Error: ya existe este fichero. Y no se permite reescribirlo';
            return FALSE;
        }
        return TRUE;
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

    /**
     * Guardar el archivo en el servidor
     * 
     * @param string $name nombre con el que se guardará el archivo
     * @return boolean
     */
    protected abstract function saveFile($name);
}
