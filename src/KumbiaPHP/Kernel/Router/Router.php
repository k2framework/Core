<?php

namespace KumbiaPHP\Kernel\Router;

use KumbiaPHP\Kernel\Router\RouterInterface;
use KumbiaPHP\Kernel\AppContext;
use KumbiaPHP\Kernel\RedirectResponse;
use KumbiaPHP\Kernel\KernelInterface;
use KumbiaPHP\Kernel\Request;

/**
 * Servicio Router del framework
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

    /**
     * Redirije la petición a otro modulo/controlador/accion de la aplicación.
     * @param string $url
     * @return \KumbiaPHP\Kernel\RedirectResponse 
     */
    public function redirect($url = NULL, $status = 302)
    {
        $url = $this->app->getBaseUrl() . ltrim($url, '/');
        return new RedirectResponse($url, $status);
    }

    /**
     * Redirije la petición a otra acción del mismo controlador.
     * @param type $action
     * @return \KumbiaPHP\Kernel\RedirectResponse 
     */
    public function toAction($action = NULL, $status = 302)
    {
        $url = $this->app->getControllerUrl() . '/' . $action;
        return new RedirectResponse($url, $status);
    }

    /**
     * Redirije la petición a otro modulo/controlador/accion de la aplicación internamente,
     * es decir, la url del navegador no va a cambiar para el usuario.
     * @param type $url
     * @return type
     * @throws \LogicException 
     */
    public function forward($url)
    {
        if ($this->forwards++ > 10) {
            throw new \LogicException("Se ha detectado un ciclo de redirección Infinito...!!!");
        }
        //obtengo el request y le asigno la nueva url.
        $request = \KumbiaPHP\Kernel\Kernel::get('request');
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