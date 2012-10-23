<?php

namespace KumbiaPHP\Compiler;

use KumbiaPHP\Compiler\CompilerInterface;
use KumbiaPHP\Compiler\CompilerException;

class Compiler implements CompilerInterface
{

    protected $filename;
    protected $code;

    /**
     * Arreglo con las rutas donde se van a buscar las clases.
     * @var array
     */
    private $directories = array();

    function __construct($filename, $autoload = FALSE)
    {
        if (!is_dir($dir = dirname($filename))) {
            throw new CompilerException("El directorio \"$dir\" No existe");
        }
        $this->filename = $filename;

        if (TRUE === $autoload) {
            spl_autoload_register(array($this, 'autoload'), TRUE, TRUE);
        }
    }

    /**
     * registra rutas donde se buscarán clases.
     */
    public function registerDirectories($directories)
    {
        $this->directories = $directories;
    }

    public function add($filename)
    {
        if (!is_dir($dir = dirname($filename))) {
            throw new CompilerException("El directorio \"$dir\" No existe");
        }
        if (!is_writable($filename)) {
            throw new CompilerException("No se puede escribir en el Archvio \"$filename\"");
        }

        $code = file_get_contents($filename);
        $this->code = $code . PHP_EOL . PHP_EOL . $this->code;
    }

    public function autoload($className)
    {
        if (in_array($className, $this->excludedClasses())) {
            return;
        }
        $className = ltrim($className, '\\');
        $fileName = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach ($this->directories as $folder) {
            if (file_exists($file = $folder . DIRECTORY_SEPARATOR . $fileName)) {
                $this->add($file);
                return;
            }
        }
    }

    public function compile()
    {
        $compiled = str_replace('<?php', '', $this->code);

        $compiled = preg_replace('@/\*(.*)\*/@Us', '', $compiled);

        file_put_contents($this->filename, "<?php$compiled");
    }

    protected function excludedClasses()
    {
        return array(
            'KumbiaPHP\\Kernel\\Exception\\ExceptionHandler',
        );
    }

}