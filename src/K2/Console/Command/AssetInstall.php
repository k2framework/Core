<?php

namespace K2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use K2\Kernel\App;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of AssetInstall
 *
 * @author manuel
 */
class AssetInstall extends Command
{

    protected $publicDir;

    protected function configure()
    {
        $this->setName('asset:install')
                ->setDescription("Instala todos los archivos que se encuentran en la carpeta public De cada módulo en la carpeta public/nombremodulo del public de la app");

        $this->publicDir = dirname(APP_PATH) . '/public/';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();

        foreach (App::modules() as $name => $module) {

            if (is_dir($target = $this->publicDir . strtolower($module['name']))) {

                $filesystem->remove($target);

                $output->writeln("Removiendo la carpeta {$target}");
            }

            if (is_dir($from = rtrim($module['path'], '/') . '/public/')) {

                $filesystem->mirror($from, $target);

                $output->writeln("Copiado el modulo {$module['name']}");
            }
        }
        $output->writeln("Se instalaron todos los assets correctamente!!!");
    }

}
