<?php

namespace K2\View\Twig\Extension;

use K2\Kernel\App;

class Core extends \Twig_Extension
{

    public function getName()
    {
        return 'k2_extension';
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            'url' => new \Twig_Function_Method($this, 'url'),
            'k2_memory_usage' => new \Twig_Function_Method($this, 'memoryUsage'),
            'k2_execution_time' => new \Twig_Function_Method($this, 'executionTime'),
            new \Twig_SimpleFunction('asset', function($file) {
                        return PUBLIC_PATH . $file;
                    }),
        );
    }

    public function getGlobals()
    {
        return array(
            'app' => array(
                'context' => \K2\Kernel\App::getContext(),
                'request' => \K2\Kernel\App::getRequest(),
                'user' => \K2\Kernel\App::getUser(),
                'messages' => App::get('flash')->getAll(),
            ),
        );
    }

    public function url($url = false, $module = false, $controller = false, $action = false, array $parameters = array())
    {
        if (0 === func_num_args()) {
            //si no se envió nada, se usa la url actual
            return rtrim(PUBLIC_PATH, '/') . App::getRequest()->getRequestUrl();
        } elseif (false !== $url) {
            //si se envió un string, se devuelve como una url
            return PUBLIC_PATH . ltrim($url);
        } else {
            //si no se envió un string, sino parametros con nombres, se crea la url
            $url = '';
            $context = App::getContext();

            //solo si no se especifica el modulo podemos usar el controlador y accion actual
            //de no ser especificados estos.
            if (!$module) {
                $controller || $controller = $context['controller'];
                $action || $action = $context['action'];
            } else {
                //de lo contrario, se asignaran valores por defecto a controller y action
                $controller || $controller = 'index';
                $action || $action = 'index';
            }

            $module = $module ? : $context['module']['name'];

            $url .= App::prefix($module) . '/';

            $url .= $controller . '/';
            $url .= $action . '/';
            $url .= join('/', $parameters);

            return PUBLIC_PATH . ltrim($url, '/');
        }
    }

    public function memoryUsage()
    {
        return number_format(memory_get_usage() / 1048576, 2);
    }

    public function executionTime()
    {
        return round((microtime(1) - START_TIME), 4);
    }

}
