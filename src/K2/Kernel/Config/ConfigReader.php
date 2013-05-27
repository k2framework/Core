<?php

namespace K2\Kernel\Config;

use K2\Kernel\App;

class ConfigReader
{

    protected $file;

    public function __construct()
    {
        $this->file = APP_PATH . 'temp/cache/config.php';
        if (PRODUCTION) {
            if ($this->isCompiled()) {
                App::addDefinitions(require $this->file);
            } else {
                $data['definitions'] = App::definitions();
                $config = PHP_EOL . PHP_EOL . 'return ' . var_export($data, true);
                file_put_contents($this->file, "<?php$config;");
            }
        } else {
            if (is_writable($this->file)) {
                unlink($this->file);
            }
        }
    }

    /**
     * Este metodo deberá unificar toda la configuración de cada
     * modulo en un solo esquema
     *  
     */
    protected function compile()
    {
        
    }

    public function isCompiled()
    {
        return PRODUCTION && is_file($this->file);
    }

}