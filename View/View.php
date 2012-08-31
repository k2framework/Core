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
        ;

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

        if ($response instanceof Response) {
            $response->setContent($content);
            return $response;
        }

        return new Response($content);
    }

    protected function findTemplate($template)
    {
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = $this->container->get('app.context');

        $template = explode(':', $template);

        if (count($template) > 1) {
            $module = rtrim($app->getModules($template[0]), '/') . '/' . $app->getCurrentModule();
            $file = $module . '/View/_shared/templates/' . $template[1] . '.phtml';
        } else {
            $file = rtrim($app->getAppPath(), '/') . '/view/templates/' . $template[0] . '.phtml';
        }
        if (!file_exists($file)) {
            throw new \LogicException(sprintf("No existe El Template <b>%s</b> en <b>%s</b>", basename($file), $file));
        }
        return $file;
    }

    protected function findView($view)
    {
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = $this->container->get('app.context');
        $module = $app->getCurrentModule();
        $controller = $app->getCurrentController();
        $file = rtrim($app->getModules($module), '/') . '/' . $module .
                '/View/' . $controller . '/' . $view . '.phtml';
        if (!file_exists($file)) {
            throw new \LogicException(sprintf("No existe la Vista <b>%s</b> en <b>%s</b>", basename($file), $file));
        }
        return $file;
    }

}