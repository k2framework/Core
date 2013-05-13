<?php

namespace K2\View\Twig;

use K2\Kernel\App;

class Extension extends \Twig_Extension
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
            new \Twig_SimpleFunction('asset', function($file) {
                        return PUBLIC_PATH . $file;
                    }),
            'url' => new \Twig_Function_Method($this, 'url'),
            'k2_memory_usage' => new \Twig_Function_Method($this, 'memoryUsage'),
            'k2_execution_time' => new \Twig_Function_Method($this, 'executionTime'),
        );
    }

    public function getGlobals()
    {
        return array(
            'app' => array(
                'context' => \K2\Kernel\App::getContext(),
                'request' => \K2\Kernel\App::getRequest(),
                'user' => \K2\Kernel\App::getUser(),
            ),
        );
    }

    public function url($url = false, $module = false, $controller = false, $action = false, array $parameters = array())
    {
        if (0 === func_num_args()) {
            //si no se envió nada, se usa la url actual
            return rtrim(PUBLIC_PATH, '/') . App::getRequest()->getRequestUrl();
        } elseif ($url) {
            //si se envió un string, se devuelve como una url
            return PUBLIC_PATH . ltrim($url);
        } else {
            //si no se envió un string, sino parametros con nombres, se crea la url
            $url = '';
            $context = App::getContext();

            $module = $module ? : $context['module']['name'];

            $url .= App::prefix($module) . '/';

            $url .= ($controller ? : $context['controller']) . '/';
            $url .= ($action ? : $context['action']) . '/';
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
