<?php

namespace K2\Kernel\Config;

use K2\Kernel\App;

class ConfigReader
{

    public static $file;

    public static function getCompiled()
    {
        if (static::isCompiled()) {
            $data = require static::$file;
            App::addDefinitions($data['definitions']);
            App::modules($data['modules']);
            return true;
        } else {
            if (is_writable(static::$file)) {
                unlink(static::$file);
            }
        }
        return false;
    }

    /**
     * Este metodo deberá unificar toda la configuración de cada
     * modulo en un solo esquema
     *  
     */
    public static function compile()
    {
        $modules = App::modules();
        echo "<pre>";
        $code = "<?php" . PHP_EOL;
        foreach ($modules as $name => $congif) {
            $file = file_get_contents($congif['file']);
            
            $file = preg_replace(array('/<\?php/','/\?>/'), '', $file);
            
            $file = preg_match_all('/return(\s+)array(\s.)\((.+)\)/m', $file, $matchs);
            
            var_dump($matchs);
            
            $code .= $file . PHP_EOL;
        }
        //$config = PHP_EOL . 'return ' . var_export($data, true);
        echo $code;
        file_put_contents(static::$file, $code);
        die;
    }

    public static function isCompiled()
    {
        return PRODUCTION && is_file(static::$file);
    }

}

ConfigReader::$file = APP_PATH . 'temp/cache/config.php';