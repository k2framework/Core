<?php

namespace K2\View;

use K2\Kernel\App;
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

        if (false !== $view) {

            if (null == $view) {
                $context = App::getContext();
                $view = '@' . trim($context['module']['name'], '/') . '/'
                        . $context['controller'] . '/' . $context['action'];
            }

            if (isset($params['response'])) {
                $view .= $params['response'];
            }

            $view .= '.twig';
            try {
                $content = $this->twig->render($view, $variables);
            } catch (\Twig_Error_Loader $e) {
                if (!isset($variables['scaffold'])) {
                    throw $e;
                }

                //si se usa scaffold, buscamos en views/scaffold
                $view = explode('/', $view);
                $view = basename(end($view), '.twig');
                $view = '/scaffolds/' . $variables['scaffold'] . '/' . $view;

                if (isset($params['response'])) {
                    $view .= $params['response'];
                }

                $view .= '.twig';

                $content = $this->twig->render($view, $variables);
            }
        } else {
            $content = null;
        }

        $response = new Response($content);
        $response->setCharset($this->twig->getCharset());
        $response->cache(isset($params['time']) ? $params['time'] : null);

        return $response;
    }

}