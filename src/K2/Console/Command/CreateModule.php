<?php

namespace K2\Console\Command;

use K2\Kernel\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Description of ListModules
 *
 * @author maguirre
 */
class CreateModule extends Command
{

    protected function configure()
    {
        $this->setName('module:create')
                ->setDescription("Crea un Nuevo Modulo")
                ->addOption('namespace', null, InputOption::VALUE_OPTIONAL
                        , 'Namespace de Modulo')
                ->addOption('name', null, InputOption::VALUE_OPTIONAL
                        , 'Nombre del Modulo')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modules = App::modules();

        $this->interact($input, $output);

        $dialog = $this->getHelper('dialog');

        $namespace = $dialog->askAndValidate($output, "\nNamespace del Modulo [{$input->getOption('namespace')}]: "
                , function($namespace) use ($output, $modules) {

                    $namespace = trim($namespace, '\\');

                    if ('' === $namespace) {
                        $output->writeln("(Error) Namespace Invalido!");
                        return 1;
                    }

                    foreach ($modules as $config) {
                        if (strtolower($namespace) === strtolower(trim($config['namespace'], '\\'))) {
                            $output->writeln("(Error) El namespace ya esta siendo usado por el modulo {$config['name']}!");
                            return 1;
                        }
                    }

                    return $namespace;
                }, false, $input->getOption('namespace'));

        $input->setOption('namespace', $namespace);

        if (!$input->getOption('name')) {
            $input->setOption('name', str_replace('\\', '', $namespace));
        }

        $name = $dialog->askAndValidate($output, "\nNombre del Modulo [{$input->getOption('name')}]: "
                , function($name) use ($output, $modules) {

                    $name = trim($name);

                    if ('' === $name) {
                        $output->writeln("(Error) Nombre Invalido!");
                        return 1;
                    }

                    foreach ($modules as $moduleName => $config) {
                        if (strtolower($name) === strtolower(trim($moduleName))) {
                            $output->writeln("(Error) El nombre ya esta siendo usado por el modulo {$moduleName}!");
                            return 1;
                        }
                    }

                    return $name;
                }, false, $input->getOption('name'));

        $input->setOption('name', $name);

        $path = APP_PATH . 'modules/' . str_replace('\\', '/', $namespace);

        $output->writeln($path);

        $filesystem = App::get("filesystem");

        if ($filesystem->exists($path)) {
            $output->writeln("(Error) El Directorio {$path} ya existe!");
            return 1;
        }

        $output->writeln("\nCreando Los directorios del Modulo\n");

        $template = App::get('twig')->loadTemplate('@K2Core/console/module/all.twig');

        $data = $input->getOptions();

        $controller = $template->renderBlock('controller', $data);
        $config = $template->renderBlock('config', $data);
        $view = $template->renderBlock('view', $data);

        $filesystem->mkdir($path);
        $filesystem->mkdir($path . '/Controller');
        $filesystem->mkdir($path . '/View/index');

        file_put_contents($path . '/Controller/indexController.php', $controller);
        file_put_contents($path . '/config.php', $config);
        file_put_contents($path . '/View/Index/index.twig', $view);
        
        $output->writeln("\nEl modulo se ha creado exitosamente\n");
    }

}
