<?php

namespace K2\Console\Command;

use K2\Kernel\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of ListModules
 *
 * @author maguirre
 */
class ListModules extends Command
{

    protected function configure()
    {
        $this->setName('module:list')
                ->setDescription("Devuelve un listados con los módulos instalador en la App")
                ->addOption('path', null, InputOption::VALUE_NONE
                        , 'Traer los path de los modulos en vez de los refijos de url');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modules = App::modules();
        $routes = array_flip(App::routes());

        $output->writeln('');

        if ($input->getOption('path')) {
            foreach ($modules as $name => $config) {
                $write = str_pad("<info>$name</info>", 30, ' ') . ' => ';
                $write .= (isset($config['path']) ? $config['path'] : '');
                $output->writeln($write);
            }
        } else {
            foreach ($modules as $name => $config) {
                $write = str_pad("<info>$name</info>", 30, ' ') . ' => ';
                $write .= isset($routes[$name]) ? $routes[$name] : 'No.';
                $output->writeln($write);
            }
        }
    }

}
