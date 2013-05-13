<?php

namespace K2\View\Twig;

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
                        return \K2\Kernel\App::getRequest()->getBaseUrl() . $file;
                    }),
            new \Twig_SimpleFunction('url', function($route) {
                        return \K2\Kernel\App::getContext()->createUrl($route);
                    }),
            new \Twig_SimpleFunction('url_action', function($action) {
                        return \K2\Kernel\App::getContext()->getControllerUrl($action);
                    }),
            'k2_memory_usage' => new \Twig_Function_Method($this, 'memoryUsage'),
            'k2_execution_time' => new \Twig_Function_Method($this, 'executionTime'),
        );
    }

    public function getGlobals()
    {
        return array();
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
