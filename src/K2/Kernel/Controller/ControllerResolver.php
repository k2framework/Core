<?php

namespace K2\Kernel\Controller;

use \ReflectionClass;
use \ReflectionObject;
use K2\Kernel\Response;
use K2\Di\Container\Container;
use K2\Kernel\Exception\NotFoundException;
use K2\Kernel\App;

/**
 * Description of ControllerResolver
 *
 * @author manuel
 */
class ControllerResolver
{

    /**
     *
     * @var Container 
     */
    protected $container;

    /**
     * Nombre del Módulo que se está ejecutando
     * @var \K2\Kernel\Module 
     */
    protected $module;

    /**
     * Instancia del Controlador a ejecutar
     * @var Controller 
     */
    protected $controller;

    /**
     * Contiene el nombre del controlador a ejecutar
     * @var string 
     */
    protected $controllerName;

    /**
     * Contiene el nombre de la acción a ejecutar
     * @var string 
     */
    protected $action;

    /**
     * Parametros que se le pasarán a la accion del controlador
     * @var array 
     */
    protected $parameters;

    public function __construct(Container $con)
    {
        $this->container = $con;

        $context = App::getContext();

        $this->module = $context['module'];
        $this->controllerUrl = $context['controller'];
        $this->action = $context['action'] . '_action';
        $this->parameters = $context['parameters'];
        if ('/logout' === App::getRequest()->getRequestUrl()) {
            throw new NotFoundException("La ruta \"/logout\" no concuerda con ningún módulo ni controlador en la App");
        }
    }

    /**
     * Devuelve la instancia del controlador, si no existe la crea.
     * @return Controller la instancia del controlador en ejecución
     * @throws NotFoundException si no encuentra el controlador
     */
    public function getController()
    {
        if ($this->controller instanceof Controller) {
            return $this->controller;
        }

        $module = App::getContext('module');

        $this->controllerName = App::getContext('controller') . 'Controller';

        //uno el namespace y el nombre del controlador.
        $controllerClass = $module['namespace'] . "\\Controller\\{$this->controllerName}";

//        if ($this->module->hasChildren()) {
//            $children = $this->module->getChildren();
//            $childrenControllerClass = $children->getNamespace() . "\\Controller\\{$this->controllerName}";
//            if (class_exists($childrenControllerClass)) {
//                $controllerClass = $childrenControllerClass;
//                $this->module = $children;
//            }
//        }

        if (!class_exists($controllerClass)) {
            throw new NotFoundException(sprintf("No existe el controlador \"%s\" en la ruta \"%s/Controller/%s.php\"", $this->controllerName, $module['path'], $this->controllerName));
        }

        $reflectionClass = new ReflectionClass($controllerClass);

        $this->controller = $reflectionClass->newInstance();

        return $this->controller;
    }

    /**
     * Ejecuta la acción en el controlador, y los filtros.
     */
    public function executeAction()
    {
        $this->getController();

        $controller = new ReflectionObject($this->controller);

        if (($response = $this->executeBeforeFilter($controller)) instanceof Response) {
            return $response;
        }

        if (false === $this->action) {
            return; //si el before devuelve false, es porque no queremos que se ejecute nuestra acción.
        }
        $this->validateAction($controller, $this->parameters);

        $response = call_user_func_array(array($this->controller, $this->action), $this->parameters);

        $this->executeAfterFilter($controller);

        return $response;
    }

    /**
     * Valida la acción a ejecutar, que sea publica y que los parametros esperados
     * sean correctos.
     * @param \ReflectionObject $controller Reflección hacia el controller.
     * @param array $params Parametros a pasar al método
     * @throws NotFoundException si no se cumplen las condiciones para llamar a la acción.
     */
    protected function validateAction(\ReflectionObject $controller, array $params)
    {
        if ($controller->hasProperty('limitParams')) {
            $limitParams = $controller->getProperty('limitParams');
            $limitParams->setAccessible(true);
            $limitParams = $limitParams->getValue($this->controller);
        } else {
            $limitParams = true; //por defeto siempre limita los parametro
        }

        if ($controller->hasProperty('parameters')) {
            $parameters = $controller->getProperty('parameters');
            $parameters->setAccessible(true);
            $parameters->setValue($this->controller, $params);
        }
        //verificamos la existencia del metodo.
        if (!$controller->hasMethod($this->action)) {
            throw new NotFoundException(sprintf("No existe el metodo \"%s\" en el controlador \"%s\"", $this->action, $this->controllerName));
        }

        $reflectionMethod = $controller->getMethod($this->action);

        //el nombre del metodo debe ser exactamente igual al camelCase
        //de la porcion de url
        if ($reflectionMethod->getName() !== $this->action) {
            throw new NotFoundException(sprintf("No existe el metodo <b>%s</b> en el controlador \"%s\"", $this->action, $this->controllerName));
        }

        //se verifica que el metodo sea public
        if (!$reflectionMethod->isPublic()) {
            throw new NotFoundException(sprintf("Éstas Tratando de acceder a un metodo no publico \"%s\" en el controlador \"%s\"", $this->action, $this->controllerName));
        }

        /**
         * Verificamos que los parametros coincidan 
         */
        if ($limitParams && (count($params) < $reflectionMethod->getNumberOfRequiredParameters() ||
                count($params) > $reflectionMethod->getNumberOfParameters())) {

            throw new NotFoundException(sprintf("Número de parámetros erróneo para ejecutar la acción \"%s\" en el controlador \"%sr\"", $this->action, $this->controllerName));
        }
    }

    /**
     * Valida y ejecuta el beforeFilter del controller si existe.
     * @param ReflectionObject $controller
     * @return \K2\Kernel\Response|null Si se retorna una respuesta, no se ejecutan
     * ni la acción del controlador ni el afterFilter
     * @throws NotFoundException si el método devuelve un string y este no concuerda 
     * con una acción válida
     */
    protected function executeBeforeFilter(ReflectionObject $controller)
    {
        if ($controller->hasMethod('beforeFilter')) {
            $method = $controller->getMethod('beforeFilter');
            $method->setAccessible(true);

            if (null !== $result = $method->invoke($this->controller)) {
                if (false === $result) {
                    //si el resultado es false, es porque no queremos que se ejecute la acción
                    $this->action = false;
                    $this->container->get('app.context')->setCurrentAction(false);
                    return;
                }
                if ($result instanceof Response) {
                    return $result; //devolvemos el objeto Response.
                }
                if (!is_string($result)) {
                    throw new NotFoundException(sprintf("El método \"beforeFilter\" solo puede devolver un <b>false, una cadena, ó un objeto Response<b> en el Controlador \"%s\"", $this->controllerName));
                }
                if (!$controller->hasMethod($result)) {
                    throw new NotFoundException(sprintf("El método \"beforeFilter\" está devolviendo el nombre de una acción inexistente \"%s\" en el Controlador \"%s\"", $result, $this->controllerName));
                }
                //si el beforeFilter del controlador devuelve un valor, el mismo será
                //usado como el nuevo nombre de la acción a ejecutar.
                $this->action = $result;
                $this->container->get('app.context')->setCurrentAction($result);
            }
        }
    }

    /**
     * Ejecuta el método afterFilter del controlador si existe.
     * @param ReflectionObject $controller 
     */
    protected function executeAfterFilter(ReflectionObject $controller)
    {
        if ($controller->hasMethod('afterFilter')) {
            $method = $controller->getMethod('afterFilter');
            $method->setAccessible(true);
            $method->invoke($this->controller);
        }
    }

    /**
     * Permite llamar a métodos protegidos del controlador.
     * @param string $method nombre del método
     * @return mixed resultado de la invocación. 
     */
    public function callMethod($method)
    {
        $reflection = new \ReflectionClass($this->controller);

        if ($reflection->hasMethod($method)) {

            //obtengo el parametro del controlador.
            $method = $reflection->getMethod($method);

            //lo hago accesible para poderlo leer
            $method->setAccessible(true);

            //y retorno su valor
            return $method->invoke($this->controller);
        } else {
            return null;
        }
    }

}