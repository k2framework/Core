<?php

namespace K2\Compiler;

use K2\Compiler\CompilerInterface;
use K2\Compiler\CompilerException;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Output\OutputInterface;

class Compiler implements CompilerInterface
{

    protected $filename;
    protected $code;
    protected $config;

    /**
     *
     * @var ClassLoader
     */
    protected $loader;

    /**
     * Arreglo con las rutas donde se van a buscar las clases.
     * @var array
     */
    private $directories = array();

    function __construct(ClassLoader $loader, $filename)
    {
        if (!is_dir($dir = dirname($filename))) {
            throw new CompilerException("El directorio \"$dir\" No existe");
        }
        $this->filename = $filename;

        $this->loader = $loader;

        $this->config = parse_ini_file('compiler.ini', TRUE);
        $this->code = "<?php\n";
    }

    public function add($filename)
    {
        if (!is_dir($dir = dirname($filename))) {
            throw new CompilerException("El directorio \"$dir\" No existe");
        }
        if (!is_writable($filename)) {
            throw new CompilerException("No se puede escribir en el Archvio \"$filename\"");
        }

        $this->code .= PHP_EOL . file_get_contents($filename);
    }

    public function compile(OutputInterface $output = null)
    {
        foreach ($this->includedClasess() as $class) {
            if ($output) {
                $output->writeln("Compilando: " . $this->loader->findFile($class));
            } else {
                var_dump($this->loader->findFile($class));
            }
            $this->add($this->loader->findFile($class));
        }

        $compiled = str_replace("\n<?php", '', $this->code);

        $compiled = preg_replace('@/\*(.*)\*/@Us', '', $compiled);

        file_put_contents($this->filename, $compiled);
    }

    protected function includedClasess()
    {
        return $this->config['include']['class'];
    }

}