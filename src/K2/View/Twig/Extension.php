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
            new \Twig_SimpleFunction('url', function($route = null) {
                        if (null === $route) {
                            return App::getContext()->getCurrentUrl();
                        } else {
                            return App::get('router')->createUrl($route);
                        }
                    }),
            new \Twig_SimpleFunction('url_action', function($action) {
                        return App::getContext()->getControllerUrl($action);
                    }),
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

    public function memoryUsage()
    {
        return number_format(memory_get_usage() / 1048576, 2);
    }

    public function executionTime()
    {
        return round((microtime(1) - START_TIME), 4);
    }

}
