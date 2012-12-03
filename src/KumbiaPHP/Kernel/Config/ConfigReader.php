<?php

namespace KumbiaPHP\Kernel\Config;

use KumbiaPHP\Kernel\AppContext;

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

//    private $sectionsValid = array('config', 'parameters');

    public function __construct(AppContext $app)
    {
        $configFile = $app->getAppPath() . '/config/config.php';
        if ($app->inProduction()) {
            if (is_file($configFile)) {
                $this->config = require $configFile;
                return;
            } else {
                $this->config = $this->compile($app);
                $config = PHP_EOL . PHP_EOL . 'return '
                        . var_export($this->config, true);
                file_put_contents($configFile, "<?php$config;");
            }
        } else {
            $this->config = $this->compile($app);
            if (is_writable($configFile)) {
                unlink($configFile);
            }
        }
    }

    /**
     * Este metodo deberá unificar toda la configuración de cada
     * modulo en un solo esquema
     *  
     */
    protected function compile(AppContext $app)
    {
        $parameters = array();
        $services = array();

        $dirs = array_merge($app->getModules(), array('app' => dirname($app->getAppPath())));

        foreach ($dirs as $namespace => $dir) {
            $configFile = rtrim($dir, '/') . '/' . $namespace . '/config/config.ini';
            $servicesFile = rtrim($dir, '/') . '/' . $namespace . '/config/services.ini';

            if (is_file($configFile)) {
                foreach (parse_ini_file($configFile, true) as $sectionType => $values) {

                    foreach ($values as $index => $v) {
                        $parameters[$sectionType][$index] = $v;
                    }
                }
            }
            if (is_file($servicesFile)) {
                foreach (parse_ini_file($servicesFile, TRUE) as $serviceName => $config) {
                    $services[$serviceName] = $config;
                }
            }
        }
//           var_dump($this->prepareAditionalConfig(array(
//            'parameters' => $parameters,
//            'services' => $services,
//        )));die;
        return $this->prepareAditionalConfig(array(
            'parameters' => $parameters,
            'services' => $services,
        ));
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Añade configuraciones adicionales al arreglo de los servicios y patametros,
     * como por ejemplo configurar el servicio que reescribe las url, para que sea
     * el primero en ejecutarse, configurar el servicio de traducciones, etc.
     * @param array $configs recibe las configuraciones de servicios y parametros
     * @return array devuelve las configuraciones actualizadas
     */
    protected function prepareAditionalConfig($configs)
    {
        //si se usa el routes lo añadimos al container
        if (isset($configs['parameters']['config']['routes'])) {
            $router = substr($configs['parameters']['config']['routes'], 1);

            //si es el router por defecto quien reescribirá las url
            if ('router' === $router) {
                //solo le añadimos un listener.
                $configs['services']['router']
                        ['listen']['rewrite'] = 'kumbia.request';
            } else/* if (isset($configs['services'][$router])) */ {
                //si es un servicio distinto al router. y existe,
                //lo añadimos al principio de todos los servicios.
                $def = $configs['services'][$router]; //guardamos la definición del servicio en una variable temporal.
                unset($configs['services'][$router]); //eliminamos la definición del arreglo $config
                //volteamos el arreglo de servicios, para insertar de nuevo la definición.
                $configs['services'] = array_reverse($configs['services'], true);
                //insertamos la definición, quedando esta al final del array.
                $configs['services'][$router] = $def;
                //volvemos a voltear los servicios, con lo que la definición insertada
                //queda de primera.
                $configs['services'] = array_reverse($configs['services'], true);
                //esto es importante debido a que queremos que el primer escucha que siempre
                //se ejecute sea el que hace las reescrituras de url, para que cuando se
                //llamen a los siguientes escuchas ya la url esté reescrita.
            }
        }

        //si se estan usando locales y ningun módulo a establecido una definición para
        //el servicio translator, lo hacemos por acá.
        if (isset($configs['parameters']['config']['locales'])
                && !isset($configs['services']['translator'])) {
            $configs['services']['translator'] = array(
                'class' => 'KumbiaPHP\\Translation\\Translator',
            );
        }

        return $configs;
    }

}