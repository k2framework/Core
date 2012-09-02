<?php

namespace KumbiaPHP\View;

use KumbiaPHP\Kernel\Response;
use KumbiaPHP\View\ViewContainer;
use KumbiaPHP\Di\Container\ContainerInterface;

/**
 * Description of Template
 *
 * @author manuel
 */
class View
{

    protected $template;
    protected $view;
    protected $variables;

    /**
     * 
     * @var ContainerInterface 
     */
    private $container;

    /**
     * @Service(container,$container)
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->variables['view'] = new ViewContainer($container);
    }

    public function render($template, $view, array $params = array(), Response $response = NULL)
    {
        $this->template = $template;
        $this->view = $view;
        $this->variables = array_merge($params, $this->variables);

        return $this->getContent($response);
    }

    protected function getContent(Response $response = NULL)
    {
        extract($this->variables, EXTR_OVERWRITE);
        $content = NULL;

        //si va a mostrar vista
        if ($this->view !== NULL) {

            ob_start();
            require_once $this->findView($this->view);
            $this->variables['view']->content = $content = ob_get_clean();
        }
        if ($this->template !== NULL) {

            ob_start();
            require_once $this->findTemplate($this->template);
            $content = ob_get_clean();
        }

        if (!$response instanceof Response) {
            $response = new Response($content);
            $response->setCharset($this->container->getParameter('config.charset'));
        }

        return $response;
    }

    protected function findTemplate($template)
    {
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = $this->container->get('app.context');

        $template = explode(':', $template);

        if (count($template) > 1) {
            $modulePath = rtrim($app->getModulesPath(), '/') . '/' . $template[0];
            $file = $modulePath . '/View/_shared/templates/' . $template[1] . '.phtml';
        } else {
            $file = rtrim($app->getAppPath(), '/') . '/view/templates/' . $template[0] . '.phtml';
        }
        if (!file_exists($file)) {
            throw new \LogicException(sprintf("No existe El Template \"%s\" en \"%s\"", basename($file), $file));
        }
        return $file;
    }

    protected function findView($view)
    {
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = $this->container->get('app.context');
        $module = $app->getCurrentModule();
        $controller = $app->getCurrentController();
        $file = rtrim($app->getModules($module), '/') . '/View/' . $controller . '/' . $view . '.phtml';
        if (!file_exists($file)) {
            throw new \LogicException(sprintf("No existe la Vista \"%s\" en \"%s\"", basename($file), $file));
        }
        return $file;
    }

}

/**
 * Atajo para htmlspecialchars, por defecto toma el charset de la
 * aplicacion
 *
 * @param string $s
 * @param string $charset
 * @return string
 */
function h($s, $charset = APP_CHARSET)
{

    return htmlspecialchars($s, ENT_QUOTES, $charset);
}

/**
 * Atajo para echo + htmlspecialchars, por defecto toma el charset de la
 * aplicacion
 *
 * @param string $s
 * @param string $charset
 * @return string
 */
function eh($s, $charset = APP_CHARSET)
{

    echo htmlspecialchars($s, ENT_QUOTES, $charset);
}