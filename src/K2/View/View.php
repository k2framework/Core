<?php

namespace K2\View;

use K2\Kernel\App;
use K2\Kernel\Response;
use K2\View\Helper\AbstractHelper;

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
    protected $response;
    protected static $variables = array();
    protected static $content = '';

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
    public function render(array $params)
    {
        self::$content = null;
        $this->template = isset($params['template']) ? $params['template'] : null;
        $this->template = isset($params['template']) ? $params['template'] : null;
        $this->view = isset($params['view']) ? $params['view'] : null;
        $this->response = isset($params['response']) ? $params['response'] : null;
        self::$variables = isset($params['params']) ? (array) $params['params'] : array();

        App::getLoader()->add(null, array(__DIR__ . '/Helper/'));

        AbstractHelper::setAppContext(App::get('app.context'));

        $response = new Response($this->getContent());
        $response->setCharset(APP_CHARSET);
        $response->cache(isset($params['time']) ? $params['time'] : null);

        return $response;
    }

    public static function getVar($name)
    {
        return array_key_exists($name, self::$variables) ? self::$variables[$name] : null;
    }

    protected function getContent()
    {
        extract(self::$variables, EXTR_OVERWRITE);

        isset($scaffold) || $scaffold = false;

        //si va a mostrar vista
        if ($this->view !== null) {

            ob_start();
            if (null !== $this->response) {
                $this->view .= '.' . $this->response; //si se estableció un response, lo concatenamos con el view
            }
            include $this->findView($this->view, $scaffold);
            self::$content = ob_get_clean();
        }
        if ($this->template !== null) {

            ob_start();
            include $this->findTemplate($this->template);
            self::$content = ob_get_clean();
        }

        return self::$content;
    }

    public static function content($showFlash = false)
    {
        echo self::$content;
        self::$content = '';
        if ($showFlash) {
            try {
                $configView = App::getParameter('view');
                if (is_array($configView) && isset($configView['flash'])) {
                    self::partial($configView['flash']);
                } else {
                    self::partial('flash/messages');
                }
            } catch (\LogicException $e) {
                $message = " Para los mensjaes Flash";
                throw new \LogicException($e->getMessage() . $message);
            }
        }
    }

    /**
     * @return \K2\Flash\Flash 
     */
    public static function flash()
    {
        return App::get('flash');
    }

    /**
     * @return \K2\Kernel\AppContext
     */
    public static function app()
    {
        return App::get('app.context');
    }

    public static function partial($partial, $time = false, $params = array())
    {
        /* @var $app \K2\Kernel\AppContext */
        $app = App::get('app.context');

        if ($time || $app->InProduction()) {
            $cache = App::get('cache');
            if ($content = $cache->getContent(md5($partial), 'partials')) {
                echo $content;
                return;
            }
        }

        $partial = explode(':', $partial);

        if (count($partial) > 1) {
            $modulePath = rtrim($app->getPath($partial[0]), '/');
            $file = $modulePath . '/View/_shared/partials/' . $partial[1] . '.phtml';
        } else {
            $file = rtrim($app->getAppPath(), '/') . '/view/partials/' . $partial[0] . '.phtml';
        }

        extract($params, EXTR_OVERWRITE);

        if (!is_file($file)) {
            throw new \LogicException(sprintf("No existe El Partial \"%s\" en \"%s\"", basename($file), $file));
        }

        ob_start();

        include $file;

        echo $content = ob_get_clean();

        if ($time || $app->InProduction()) {
            $cache = App::get('cache');
            $cache->saveContent(md5(join(':', $partial)), $content, $time, 'partials');
        }
    }

    protected function findTemplate($template)
    {
        /* @var $app \K2\Kernel\AppContext */
        $app = App::get('app.context');

        $template = explode(':', $template);

        if (count($template) > 1) {
            $modulePath = rtrim(App::get('app.kernel')->getModules($template[0])->getPath(), '/');
            $file = $modulePath . '/View/_shared/templates/' . $template[1] . '.phtml';
        } else {
            $file = rtrim($app->getAppPath(), '/') . '/view/templates/' . $template[0] . '.phtml';
        }
        if (!is_file($file)) {
            throw new \LogicException(sprintf("No existe El Template \"%s\" en \"%s\"", basename($file), $file));
        }
        return $file;
    }

    protected function findView($view, $scaffold = false)
    {
        /* @var $app \K2\Kernel\AppContext */
        $app = App::get('app.context');

        $view = explode(':', $view);

        if (count($view) > 1) {
            if (3 !== count($view)) {
                $view = join(':', $view);
                throw new \LogicException("No se está especificando el \"Módulo:controlador:vista\" en el nombre de la vista correctamente $view");
            }
            $module = $view[0];
            $controller = $view[1];
            $view = $view[2];
        } else {
            $module = $app->getCurrentModule();
            $controller = $app->getCurrentController();
            $view = $view[0];
        }

        $file = rtrim(App::get('app.kernel')->getModules($module)->getPath(), '/') . '/View/' . $controller . '/' . $view . '.phtml';
        if (!is_file($file)) {
            if (is_string($scaffold)) {
                $view = '/view/scaffolds/' . $scaffold . '/' . $view . '.phtml';
                $file = rtrim($app->getAppPath(), '/') . $view;
                if (is_file($file)) {
                    return $file;
                }
            }
            throw new \LogicException(sprintf("No existe la Vista \"%s\" en \"%s\"", basename($file), $file));
        }

        return $file;
    }

}