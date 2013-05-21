<?php

namespace K2\View\Twig\Extension;

use K2\Kernel\App;

class Core extends \Twig_Extension
{

    public function getName()
    {
        return 'k2_core';
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            'url' => new \Twig_Function_Method($this, 'url'),
            'render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
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
            return App::get('router')->createUrl($url);
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

            $module = $module ? str_replace('@', '', $module) : $context['module']['name'];

            $url .= App::prefix($module) . '/';

            $url .= $controller . '/';
            $url .= $action . '/';
            $url .= join('/', $parameters);

            return PUBLIC_PATH . ltrim($url, '/');
        }
    }

    public function render($url, array $parameters = array())
    {
        return App::get('router')->forward(trim($url) . '/' . join('/', $parameters));
    }

}
