<?php

namespace K2\Twig\Extension;

use K2\Kernel\App;
use K2\Kernel\Request;
use K2\Kernel\Response;
use K2\Twig\Globals;
use K2\Kernel\Config\Reader;
use K2\Twig\TokenParser\Trans as TransToken;
use K2\Kernel\Controller\ControllerResolver;

class Core extends \Twig_Extension
{

    public function getTokenParsers()
    {
        return array(
            new TransToken(),
        );
    }

    public function getName()
    {
        return 'k2_core';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array($this, 'trans')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('url', array($this, 'url')),
            new \Twig_SimpleFunction('asset', array($this, 'asset')),
            new \Twig_SimpleFunction('render', array($this, 'render'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('isLogged', array($this, 'isLogged')),
            new \Twig_SimpleFunction('config', array($this, 'config')),
        );
    }

    public function getGlobals()
    {
        return array(
            'app' => new Globals(),
        );
    }

    public function url($url = null, $module = null, $controller = null, $action = null, array $parameters = array())
    {
        if (0 === func_num_args()) {
            //si no se envió nada, se usa la url actual
            return rtrim(PUBLIC_PATH, '/') . App::getRequest()->getRequestUrl();
        } elseif (null !== $url) {
            //si se envió un string, se devuelve como una url
            return App::get('router')->createUrl($url);
        } else {
            //si no se envió un string, sino parametros con nombres, se crea la url
            $url = array();
            $context = App::getContext();

            //solo si no se especifica el modulo podemos usar el controlador y accion actual
            //de no ser especificados estos.
            if (!$module) {
                null !== $controller || $controller = $context['controller'];
                null !== $action || $action = $context['action'];
            } else {
                //de lo contrario, se asignaran valores por defecto a controller y action
                null !== $controller || $controller = 'index';
                null !== $action || $action = 'index';
            }

            $module = $module ? str_replace('@', '', $module) : $context['module']['name'];

            $url[] = App::prefix($module);

            $url[] = $controller;
            $url[] = $action;
            $url = array_merge($url, $parameters);

            return PUBLIC_PATH . ltrim(join('/', $url), '/');
        }
    }

    public function render($url, array $parameters = array())
    {
        $url = App::get('router')->createUrl($url, false);

        App::setRequest(new Request($url));

        $resolver = new ControllerResolver(App::get('container'), null); //la acción no tendrá el sufijo _action

        $controller = $resolver->getController();

        $resolver->validateAction(new \ReflectionObject($controller), $parameters);

        $response = $response = call_user_func_array(array(
            $controller, $resolver->getAction()), $parameters);

        if (!$response instanceof Response) {
            $response = App::get('app.kernel')->createResponse($resolver);
        }

        App::terminate();

        return $response->getContent();
    }

    public function asset($url, array $parameters = array())
    {
        if (0 === strpos($url, '@')) {
            $url = explode('/', trim(substr($url, 1), '/'), 2);
            $module = strtolower($url[0]);
            if (count($url) > 1) {
                $url = trim($module, '/') . '/' . $url[1];
            } else {
                $url = trim($module, '/');
            }
        }

        return PUBLIC_PATH . trim($url, '/');
    }

    public function isLogged($rol = null)
    {
        return App::get('security')->isLogged($rol);
    }

    public function config($key)
    {
        return Reader::get("config.{$key}");
    }

    /**
     * Realiza la traducción de un texto
     * @param string $text
     * @param array $params
     * @param string $locale 
     */
    public function trans($text, array $params = array(), $locale = null)
    {
        return App::get('translator')->trans($text, $params, $locale);
    }

}
