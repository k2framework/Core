<?php

namespace KumbiaPHP\Loader;

/**
 * 
 */
final class Autoload
{

    /**
     * @var array
     */
    private static $directories = array();

    /**
     * 
     */
    public static function registerDirectories(array $directories = array())
    {
        self::$directories = array_merge(self::$directories, $directories);
    }

    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    public static function unregister()
    {
        spl_autoload_unregister(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoloader
     */
    public static function autoload($className)
    {

        $className = ltrim($className, '\\');
        $fileName = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach (self::$directories as $folder) {
            if (file_exists($file = $folder . DIRECTORY_SEPARATOR . $fileName)) {
                require $file;
                return;
            }
            //var_dump($file,$fileName);
        }
    }

}
