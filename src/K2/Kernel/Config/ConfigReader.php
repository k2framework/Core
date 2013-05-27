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
        if (PRODUCTION && !is_file(static::$file)) {
            $data['definitions'] = App::definitions();
            $data['modules'] = App::modules();
            $config = PHP_EOL . 'return ' . var_export($data, true);
            file_put_contents(static::$file, "<?php$config;");
        }
    }

    public static function isCompiled()
    {
        return PRODUCTION && is_file(static::$file);
    }

}

ConfigReader::$file = APP_PATH . 'temp/cache/config.php';