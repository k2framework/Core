<?php

namespace K2\Kernel\Config;

use K2\Kernel\App;

abstract class Reader
{

    protected static $config;

    /**
     * Lee un archivo de configuracion
     *
     * @param $file archivo .ini
     * @param boolean $force forzar lectura de .ini
     * @return array
     */
    public static function read($file, $force = false)
    {
        if (isset(self::$config[$file]) && !$force) {
            return self::$config[$file];
        }

        self::$config[$file] = parse_ini_file(App::appPath() . "config/$file.ini", true);

        return self::$config[$file];
    }

    public static function get($name = null)
    {
        $namespaces = explode('.', $name);
        switch (count($namespaces)) {
            case 3:
                if (isset(self::$config[$namespaces[0]][$namespaces[1]][$namespaces[2]])) {
                    return self::$config[$namespaces[0]][$namespaces[1]][$namespaces[2]];
                }
                break;
            case 2:
                if (isset(self::$config[$namespaces[0]][$namespaces[1]])) {
                    return self::$config[$namespaces[0]][$namespaces[1]];
                }
                break;
            case 1:
                if (isset(self::$config[$namespaces[0]])) {
                    return self::$config[$namespaces[0]];
                }
                break;
        }
        return null;
    }

}