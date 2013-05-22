<?php

namespace K2\Console;

use Symfony\Component\Console\Application as Base;
use K2\Console\Command\AssetInstall;
use K2\Console\Command\ListServices;
use K2\Console\Command\ListModules;

/**
 * Description of Application
 *
 * @author manuel
 */
class Application extends Base
{

    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), array(
            new AssetInstall(), new ListServices(), new ListModules(),
        ));
    }

}
