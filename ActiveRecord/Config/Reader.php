<?php

namespace KumbiaPHP\ActiveRecord\Config;

use \AppKernel;
use ActiveRecord\Config\Parameters;
use ActiveRecord\Config\Config;

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
        $app = AppKernel::getContainer()->get('app.context');
        $ini = $app->getAppPath() . 'config/databases.ini';
        foreach (parse_ini_file($ini, TRUE) as $configName => $params) {
            Config::add(new Parameters($configName, $params));
        }
        if (AppKernel::getContainer()->hasParameter('config.database')) {
            //lo seteamos solo si se ha definido.
            $database = AppKernel::getContainer()->getParameter('config.database');
            if ( !Config::has($database) ){
                throw new \LogicException("El valor database=$database del config.ini no concuerda con ninguna sección del databases.ini");
            }
            Config::setDefault($database);
        }
    }

}
