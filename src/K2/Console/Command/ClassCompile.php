<?php

namespace K2\Console\Command;

use K2\Kernel\App;
use K2\Compiler\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of AssetInstall
 *
 * @author manuel
 */
class ClassCompile extends Command
{

    protected function configure()
    {
        $this->setName('classes:compile')
                ->setDescription("Pasa las clases que son cargadas en todas las peticiones a un unico archivo, para evitar perder tiempo en que el aoutoload las busque y carge.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();

        $file = APP_PATH . 'kernel.min.php';

        $compiler = new Compiler(App::getLoader(), $file);

        $compiler->compile($output);
        
        $output->writeln("Se ha compilado el core del Framework");
        
        ob_end_flush();
    }

}
