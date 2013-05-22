<?php

namespace K2\Console\Command;

use K2\Kernel\App;
use K2\Kernel\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of ListServices
 *
 * @author maguirre
 */
class ListServices extends Command
{

    protected function configure()
    {
        $this->setName('service:list')
                ->setDescription("Lista los servicios disponibles en la aplicación");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        App::setRequest(new Request('/'));

        $definitions = App::get('container')->getDefinitions();

        $services = array_keys($definitions['services']);

        unset($definitions);
        
        $output->writeln('');

        foreach ($services as $id) {
            $output->writeln(str_pad("<info>$id</info>", 45, ' ') . ' => ' . get_class(App::get($id)));
        }
    }

}
