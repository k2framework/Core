<?php

namespace K2\ActiveRecord\Config;

use K2\Kernel\App;
use ActiveRecord\Config\Config;
use ActiveRecord\Config\Parameters;

/**
 * Description of Reader
 *
 * @author maguirre
 */
class Reader
{

    public static function readDatabases()
    {
        /* @var $app \K2\Kernel\AppContext */
        $app = App::get('app.context');
        $ini = $app->getAppPath() . 'config/databases.ini';
        foreach (parse_ini_file($ini, TRUE) as $configName => $params) {
            Config::add($parameter = new Parameters($configName, $params));
            if ('sqlite' === $parameter->getType()) {
                $dbName = App::appPath() . ltrim($parameter->getDbName(), '/');
                $parameter->setDbName(str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $dbName));
            }
        }
        
        $config = App::getParameter('config');
        
        if (isset($config['database'])) {
            //lo seteamos solo si se ha definido.
            if (!Config::has($config['database'])) {
                throw new \LogicException("El valor database={$config['database']} del config.ini no concuerda con ninguna sección del databases.ini");
            }
            Config::setDefaultId($config['database']);
        }
    }

}
