<?php

namespace KumbiaPHP\Kernel\Router;

use KumbiaPHP\Kernel\Router\RouterInterface;
use KumbiaPHP\Kernel\AppContext;
use KumbiaPHP\Kernel\RedirectResponse;
use KumbiaPHP\Kernel\KernelInterface;
use KumbiaPHP\Kernel\Request;

/**
 * Description of Router
 *
 * @author manuel
 */
class Router implements RouterInterface
{

    /**
     *
     * @var AppContext
     */
    protected $app;

    /**
     *
     * @var KernelInterface
     */
    protected $kernel;
    private $forwards = 0;

    public function __construct(AppContext $app, KernelInterface $kernel)
    {
        $this->app = $app;
        $this->kernel = $kernel;
    }

    public function redirect($url = NULL)
    {
        $url = $this->app->getBaseUrl() . ltrim($url, '/');
        return new RedirectResponse($url);
    }

    public function toAction($action)
    {
        $module = $this->toSmallCase($this->app->getCurrentModule());
        $controller = $this->toSmallCase($this->app->getCurrentController());

        if (count($parts = explode('/', $action)) > 1) {
            //si se están enviando parametros adicionales al nombre de la acción
            //convertimos el nombre de la accion a small_case
            $parts[0] = $this->toSmallCase($parts[0]);
            //y volvemos a construir la ruta
            $action = join('/', $parts);
        } else {
            $action = $this->toSmallCase($action);
        }

        if ($module === 'index') {
            $url = $this->app->getBaseUrl() . $controller . '/' . ltrim($action, '/');
        } else {
            $url = $this->app->getBaseUrl() . $module . '/' . $controller . '/' . ltrim($action, '/');
        }

        return new RedirectResponse($url);
    }

    public function forward($url)
    {
        if ($this->forwards++ > 10) {
            throw new \LogicException("Se ha detectado un ciclo de redirección Infinito...!!!");
        }
        //obtengo el request y le asigno la nueva url.
        $request = $this->kernel->getContainer()->get('request');
        $request->query->set('_url', $url);
        
        //retorno la respuesta del kernel.
        return $this->kernel->execute($request);
    }

    protected function toSmallCase($string)
    {
        $string[0] = strtolower($string[0]);

        return strtolower(preg_replace('/([A-Z])/', "_$1", $string));
    }

}