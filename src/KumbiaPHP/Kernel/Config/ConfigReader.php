<?php

namespace KumbiaPHP\Kernel\Config;

use KumbiaPHP\Kernel\AppContext;
use KumbiaPHP\Kernel\Collection;

/**
 * Description of ConfigContainer
 *
 * @author manuel
 */
class ConfigReader
{

    /**
     *
     * @var Collection
     */
    protected $config;
    private $sectionsValid = array('config', 'parameters');

    public function __construct(AppContext $app)
    {
        $this->config = $this->compile($app);
    }

    /**
     * Este metodo deberá unificar toda la configuración de cada
     * modulo en un solo esquema
     *  
     */
    protected function compile(AppContext $app)
    {
        $section['config'] = new Collection();
        $section['services'] = new Collection();
        $section['parameters'] = new Collection();

        $dirs = array_merge($app->getNamespaces(), array_values($app->getModules()), array($app->getAppPath()));

        foreach (array_unique($dirs) as $namespace => $dir) {
            if (is_numeric($namespace)) {
                $configFile = rtrim($dir, '/') . '/config/config.ini';
                $servicesFile = rtrim($dir, '/') . '/config/services.ini';
            } else {
                $configFile = rtrim($dir, '/') . '/' . $namespace . '/config/config.ini';
                $servicesFile = rtrim($dir, '/') . '/' . $namespace . '/config/services.ini';
            }

            if (is_file($configFile)) {
                foreach (parse_ini_file($configFile, TRUE) as $sectionType => $values) {

                    if (in_array($sectionType, $this->sectionsValid)) {
                        foreach ($values as $index => $v) {
                            $section[$sectionType]->set($index, $v);
                        }
                    }
                }
            }
            if (is_file($servicesFile)) {
                foreach (parse_ini_file($servicesFile, TRUE) as $serviceName => $config) {
                    $section['services']->set($serviceName, $config);
                }
            }
        }

        $section = $this->explodeIndexes($section);

        unset($section['config']); //esta seccion esta disponible en parameters con el prefio config.*

        return new Collection($section);
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Busca en el config.ini de la aplicación
     * los indices que representen servicios definidos, y que tengan
     * un punto que separe al nombre del servicio de un parametro del mismo
     * ( el parametro tambien debe estár definido )
     * 
     * @example
     * 
     * tenemos un servicio llamada    mi_servico
     * tiene un parametro definido    nombre_app  con valor = 'Mi App'
     *  
     * Si queremos cambiar ese valor, debemos hacerlo en el config.ini de 
     * la App.
     * 
     * y colocar los siguiente en la seccion [config]:
     * 
     * mi_servico.nombre_app = 'Nuevo nombre asignado'
     * 
     * @param Collection $services
     * @param Collection $params 
     */
    protected function explodeIndexes(array $section)
    {
        foreach ($section['config']->all() as $key => $value) {
            $explode = explode('.', $key);
            //si hay un punto y el valor delante del punto
            //es el nombre de un servicio existente
            if (count($explode) > 1 && $section['services']->has($explode[0])) {
                //le asignamos el nuevo valor al parametro
                //que usará ese servicio
                if ($section['parameters']->has($explode[1])) {
                    $section['parameters']->set($explode[1], $value);
                }
            } else {
                $section['parameters']->set('config.' . $key, $value);
            }
        }
        return $section;
    }

}