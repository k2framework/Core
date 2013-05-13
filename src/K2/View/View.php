<?php

namespace K2\View;

use K2\Kernel\Response;
use \Twig_Environment;

/**
 * Description of Template
 *
 * @author manuel
 */
class View
{

    /**
     *
     * @var Twig_Environment 
     */
    protected $twig;

    function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Devuelve un objeto response a partir de los parametros especificados en el
     * array $params.
     * 
     * Los posibles parametros que recibe el método son:
     * 
     * template: nombre del template a usar
     * response: tipo de respuesta a mostrar
     * view: nombre de la vista
     * params: arreglo con los parametros a usar en la vista
     * time: tiempo de cache para la respuesta
     * 
     * @param array $params arreglo con los parametros que usará el método render
     * @return \K2\Kernel\Response 
     */
    public function render($view, array $params = array())
    {
        $variables = isset($params['params']) ? (array) $params['params'] : array();

        if (null == $view) {
            $module = \K2\Kernel\App::getContext()->getCurrentModule()->getName();
            $controller = \K2\Kernel\App::getContext()->getCurrentController();
            $action = \K2\Kernel\App::getContext()->getCurrentAction();
            $view = '@' . trim($module, '/') . '/' . $controller . '/' . $action;
        }

        if (isset($params['response'])) {
            $view .= $params['response'];
        }

        $view .= '.twig';

        $content = $this->twig->render($view, $variables);

        $response = new Response($content);
        $config = \K2\Kernel\App::getParameter('config');
        $response->setCharset($this->twig->getCharset());
        $response->cache(isset($params['time']) ? $params['time'] : null);

        return $response;
    }

}