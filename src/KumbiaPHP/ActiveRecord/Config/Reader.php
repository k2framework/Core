<?php

namespace KumbiaPHP\ActiveRecord\Config;

use KumbiaPHP\Kernel\Kernel;
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
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = Kernel::get('app.context');
        $ini = $app->getAppPath() . 'config/databases.ini';
        foreach (parse_ini_file($ini, TRUE) as $configName => $params) {
            Config::add(new Parameters($configName, $params));
        }
        if (Kernel::getContainer()->hasParameter('config.database')) {
            //lo seteamos solo si se ha definido.
            $database = Kernel::getContainer()->getParameter('config.database');
            if ( !Config::has($database) ){
                throw new \LogicException("El valor database=$database del config.ini no concuerda con ninguna sección del databases.ini");
            }
            Config::setDefault($database);
        }
    }

}
