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

        self::$config[$file] = parse_ini_file(APP_PATH . "config/$file.ini", true);

        return self::$config[$file];
    }

    public static function get($name)
    {
        try {
            $name = '['. str_replace('.', '][', $name) . ']';
            return App::get('property_accesor')->getValue(self::$config, $name);
        } catch (\RuntimeException $e) {
            return null;
        }
    }

}