<?php

namespace KumbiaPHP\View;

use KumbiaPHP\Kernel\Response;
use KumbiaPHP\Loader\Autoload;
use KumbiaPHP\View\ViewContainer;
use KumbiaPHP\View\Helper\AbstractHelper;
use KumbiaPHP\Di\Container\ContainerInterface;

require_once 'functions.php';

/**
 * Description of Template
 *
 * @author manuel
 */
class View
{

    protected $template;
    protected $view;
    protected static $variables = array();
    protected static $content = '';

    /**
     * 
     * @var ContainerInterface 
     */
    private static $container;

    /**
     * @Service(container,$container)
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        self::$container = $container;
        define('APP_CHARSET', self::$container->getParameter('config.charset') ? : 'UTF-8');
    }

    public function render($template, $view, array $params = array(), $cacheTime = NULL)
    {
        $this->template = $template;
        $this->view = $view;
        self::$variables = array_merge($params, self::$variables);

        Autoload::registerDirectories(array(__DIR__ . '/Helper/'));

        AbstractHelper::setAppContext(self::$container->get('app.context'));

        $response = new Response($this->getContent());
        $response->setCharset(APP_CHARSET);
        $response->cache($cacheTime);

        return $response;
    }

    protected function getContent()
    {
        extract(self::$variables, EXTR_OVERWRITE);

        isset($scaffold) || $scaffold = FALSE;

        //si va a mostrar vista
        if ($this->view !== NULL) {

            ob_start();
            require_once $this->findView($this->view, $scaffold);
            self::$content = ob_get_clean();
        }
        if ($this->template !== NULL) {

            ob_start();
            require_once $this->findTemplate($this->template);
            self::$content = ob_get_clean();
        }

        return self::$content;
    }

    public static function content()
    {
        echo self::$content;
        self::$content = '';
    }

    /**
     * @return \KumbiaPHP\Flash\Flash 
     */
    public static function flash()
    {
        return self::$container->get('flash');
    }

    /**
     *
     * @param type $service
     * @return \KumbiaPHP\Kernel\Session\SessionInterface|\KumbiaPHP\Kernel\AppContext|\KumbiaPHP\Flash\Flash
     */
    public static function get($service)
    {
        return self::$container->get($service);
    }

    public static function partial($partial, $time = FALSE, $params = array())
    {
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = self::$container->get('app.context');

        if ($time || $app->InProduction()) {
            $cache = self::$container->get('cache');
            if ($content = $cache->getContent(md5($partial), 'partials')) {
                echo $content;
                return;
            }
        }

        $partial = explode(':', $partial);

        if (count($partial) > 1) {
            $modulePath = rtrim($app->getModulesPath(), '/') . '/' . $partial[0];
            $file = $modulePath . '/View/_shared/partials/' . $partial[1] . '.phtml';
        } else {
            $file = rtrim($app->getAppPath(), '/') . '/view/partials/' . $partial[0] . '.phtml';
        }

        extract($params, EXTR_OVERWRITE);

        if (!file_exists($file)) {
            throw new \LogicException(sprintf("No existe El Partial \"%s\" en \"%s\"", basename($file), $file));
        }

        ob_start();

        include $file;

        echo $content = ob_get_clean();

        if ($time || $app->InProduction()) {
            $cache = self::$container->get('cache');
            $cache->saveContent(md5(join(':', $partial)), $content, $time, 'partials');
        }
    }

    protected function findTemplate($template)
    {
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = self::$container->get('app.context');

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

    protected function findView($view, $scaffold = FALSE)
    {
        /* @var $app \KumbiaPHP\Kernel\AppContext */
        $app = self::$container->get('app.context');

        $module = $app->getCurrentModule();
        $controller = $app->getCurrentController();
        $file = rtrim($app->getModules($module), '/') . '/View/' . $controller . '/' . $view . '.phtml';
        if (!file_exists($file)) {
            if (is_string($scaffold)) {
                $view = '/view/scaffolds/' . $scaffold . '/' . $view . '.phtml';
                $file = rtrim($app->getAppPath(), '/') . $view;
                if (file_exists($file)) {
                    return $file;
                }
            }
            throw new \LogicException(sprintf("No existe la Vista \"%s\" en \"%s\"", basename($file), $file));
        }

        return $file;
    }

}