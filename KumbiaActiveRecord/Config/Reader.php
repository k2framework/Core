<?php

namespace KumbiaPHP\KumbiaActiveRecord\Config;

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
        Config::setDefault(AppKernel::getContainer()->getParameter('config.database'));
    }

}
