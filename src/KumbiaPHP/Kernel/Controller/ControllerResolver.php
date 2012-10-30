<?php

namespace KumbiaPHP\Kernel\Controller;

use \ReflectionClass;
use \ReflectionObject;
use KumbiaPHP\Kernel\Response;
use KumbiaPHP\Kernel\Event\ControllerEvent;
use KumbiaPHP\Di\Container\ContainerInterface;
use KumbiaPHP\Kernel\Exception\NotFoundException;

/**
 * Description of ControllerResolver
 *
 * @author manuel
 */
class ControllerResolver
{

    /**
     *
     * @var ContainerInterface 
     */
    protected $container;
    protected $module;
    protected $controller;
    protected $contShortName;
    protected $action;
    protected $parameters;

    public function __construct(ContainerInterface $con)
    {
        $this->container = $con;
        $this->parseUrl();
    }

    public function parseUrl()
    {
        $controller = 'Index'; //controlador por defecto si no se especifica.
        $contSmall = 'index';
        $action = 'index'; //accion por defecto si no se especifica.
        $actionSmall = 'index';
        $module = '/';
        $params = array(); //parametros de la url, de existir.
        //obtenemos la url actual de la petición.
        $currentUrl = '/' . trim($this->container->get('app.context')->getCurrentUrl(), '/');

        if (!$module = $this->getModule($currentUrl)) {
            throw new NotFoundException(sprintf("La ruta \"%s\" no concuerda con ningún módulo ni controlador en la App", $currentUrl), 404);
        }

        if ($url = explode('/', trim(substr($currentUrl, strlen($module)), '/'))) {

            //ahora obtengo el controlador
            if (current($url)) {
                //si no es un controlador lanzo la excepcion
                $controller = $this->camelcase($contSmall = current($url));
                next($url);
            }
            //luego obtenemos la acción
            if (current($url)) {
                $action = $this->camelcase($actionSmall = current($url), TRUE);
                next($url);
            }
            //por ultimo los parametros
            if (current($url)) {
                $params = array_slice($url, key($url));
            }
        }

        $this->module = $module;
        $this->contShortName = $controller;
        $this->action = $action;
        $this->parameters = $params;

        $app = $this->container->get('app.context');

        $app->setCurrentModule($module);
        $app->setCurrentController($contSmall);
        $app->setCurrentAction($actionSmall);
    }

    /**
     * Convierte la cadena con espacios o guión bajo en notacion camelcase
     *
     * @param string $s cadena a convertir
     * @param boolean $firstLower indica si es lower camelcase
     * @return string
     * */
    protected function camelcase($string, $firstLower = FALSE)
    {
        $string = str_replace(' ', '', ucwords(preg_replace('@(.+)_(\w)@', '$1 $2', strtolower($string))));

        if ($firstLower) {
            // Notacion lowerCamelCase
            $string[0] = strtolower($string[0]);
            return $string;
        } else {
            return $string;
        }
    }

    protected function getModule($url)
    {
        if (0 === strpos($url, '/logout') && 7 === strlen($url)) {
            return '/logout';
        }

        $routes = array_keys($this->container->get('app.context')->getModules());

        usort($routes, function($a, $b) {
                    return strlen($a) > strlen($b) ? -1 : 1;
                }
        );

        foreach ($routes as $route) {
            if (0 === strpos($url, $route)) {
                if ('/' === $route) {
                    return $route;
                } elseif ('/' === substr($url, strlen($route), 1) || strlen($url) === strlen($route)) {
                    return $route;
                }
            }
        }
        return FALSE;
    }

    public function getController()
    {
        if ('/logout' === $this->module) {
            throw new NotFoundException(sprintf("La ruta \"%s\" no concuerda con ningún módulo ni controlador en la App", $this->module), 404);
        }
        //creo el namespace para poder crear la instancia del controlador
        $currentPath = $this->container->get('app.context')->getModules($this->module);
        $modulesPath = $this->container->get('app.context')->getModulesPath();
        $namespace = substr($currentPath, strlen($modulesPath));
        //creo el nombre del controlador con el sufijo Controller
        $controllerName = $this->contShortName . 'Controller';
        //uno el namespace y el nombre del controlador.
        $controllerClass = str_replace('/', '\\', $namespace . 'Controller/') . $controllerName;

        try {
            $reflectionClass = new ReflectionClass($controllerClass);
        } catch (\Exception $e) {
            throw new NotFoundException(sprintf("No exite el controlador \"%s\" en la ruta \"%sController/%s.php\"", $controllerName, $currentPath, $controllerName), 404);
        }

        $this->controller = $reflectionClass->newInstanceArgs(array($this->container));
        $this->setViewDefault($this->action);

        return array($this->controller, $this->action, $this->parameters);
    }

    public function executeAction(ControllerEvent $controllerEvent)
    {
        $this->controller = $controllerEvent->getController();
        $this->action = $controllerEvent->getAction();

        $controller = new ReflectionObject($this->controller);

        if (($response = $this->executeBeforeFilter($controller)) instanceof Response) {
            return $response;
        }

        if (FALSE === $this->action) {
            return NULL; //si el before devuelve false, es porque no queremos que se ejecute nuestra acción.
        }
        $this->validateAction($controller, $controllerEvent->getParameters());

        $response = call_user_func_array(array($this->controller, $this->action), $controllerEvent->getParameters());

        $this->executeAfterFilter($controller);

        return $response;
    }

    public function getPublicProperties()
    {
        return get_object_vars($this->controller);
    }

    public function getModulePath()
    {
        $namespaces = $this->container->get('app.context')->getModules();
        return rtrim($namespaces[$this->module] . '/') . '/' . $this->module;
    }

    protected function validateAction(\ReflectionObject $controller, array $params)
    {
        if ($controller->hasProperty('limitParams')) {
            $limitParams = $controller->getProperty('limitParams');
            $limitParams->setAccessible(true);
            $limitParams = $limitParams->getValue($this->controller);
        } else {
            $limitParams = TRUE; //por defeto siempre limita los parametro
        }

        if ($controller->hasProperty('parameters')) {
            $parameters = $controller->getProperty('parameters');
            $parameters->setAccessible(true);
            $parameters->setValue($this->controller, $params);
        }
        //verificamos la existencia del metodo.
        if (!$controller->hasMethod($this->action)) {
            throw new NotFoundException(sprintf("No exite el metodo \"%s\" en el controlador \"%sController\"", $this->action, $this->contShortName), 404);
        }

        $reflectionMethod = $controller->getMethod($this->action);

        //verificamos que no sea el constructor a quien se llama
        if ($reflectionMethod->isConstructor()) {
            throw new NotFoundException(sprintf("Se está intentando ejecutar el constructor del controlador como una acción, en el controlador \"%sController\"", $this->contShortName), 404);
        }

        if (in_array($this->action, array('beforeFilter', 'afterFilter'))) {
            throw new NotFoundException(sprintf("Se está intentando ejecutar el filtro \"%s\" del controlador \"%sController\"", $this->action, $this->contShortName), 404);
        }

        //el nombre del metodo debe ser exactamente igual al camelCase
        //de la porcion de url
        if ($reflectionMethod->getName() !== $this->action) {
            throw new NotFoundException(sprintf("No exite el metodo <b>%s</b> en el controlador \"%sController\"", $this->action, $this->contShortName), 404);
        }

        //se verifica que el metodo sea public
        if (!$reflectionMethod->isPublic()) {
            throw new NotFoundException(sprintf("Éstas Tratando de acceder a un metodo no publico \"%s\" en el controlador \"%sController\"", $this->action, $this->contShortName), 404);
        }

        /**
         * Verificamos que los parametros coincidan 
         */
        if ($limitParams && (count($params) < $reflectionMethod->getNumberOfRequiredParameters() ||
                count($params) > $reflectionMethod->getNumberOfParameters())) {

            throw new NotFoundException(sprintf("Número de parámetros erróneo para ejecutar la acción \"%s\" en el controlador \"%sController\"", $this->action, $this->contShortName), 404);
        }
    }

    protected function executeBeforeFilter(ReflectionObject $controller)
    {
        if ($controller->hasMethod('beforeFilter')) {
            $method = $controller->getMethod('beforeFilter');
            $method->setAccessible(TRUE);

            if (NULL !== $result = $method->invoke($this->controller)) {
                if (FALSE === $result) {
                    //si el resultado es false, es porque no queremos que se ejecute la acción
                    $this->action = FALSE;
                    $this->container->get('app.context')->setCurrentAction(FALSE);
                    return;
                }
                if ($result instanceof Response) {
                    return $result; //devolvemos el objeto Response.
                }
                if (!is_string($result)) {
                    throw new NotFoundException(sprintf("El método \"beforeFilter\" solo puede devolver un <b>FALSE, una cadena, ó un objeto Response<b> en el Controlador \"%sController\"", $this->contShortName));
                }
                if (!$controller->hasMethod($result)) {
                    throw new NotFoundException(sprintf("El método \"beforeFilter\" está devolviendo el nombre de una acción inexistente \"%s\" en el Controlador \"%sController\"", $result, $this->contShortName));
                }
                //si el beforeFilter del controlador devuelve un valor, el mismo será
                //usado como el nuevo nombre de la acción a ejecutar.
                $this->action = $result;
                $this->container->get('app.context')->setCurrentAction($result);
            }
        }
    }

    protected function executeAfterFilter(ReflectionObject $controller)
    {
        if ($controller->hasMethod('afterFilter')) {
            $method = $controller->getMethod('afterFilter');
            $method->setAccessible(TRUE);
            $method->invoke($this->controller);
        }
    }

    public function getParamValue($propertie)
    {
        $reflection = new \ReflectionClass($this->controller);

        if ($reflection->hasProperty($propertie)) {

            //obtengo el parametro del controlador.
            $propertie = $reflection->getProperty($propertie);

            //lo hago accesible para poderlo leer
            $propertie->setAccessible(true);

            //y retorno su valor
            return $propertie->getValue($this->controller);
        } else {
            return NULL;
        }
    }

    protected function setViewDefault($action)
    {
        $reflection = new \ReflectionClass($this->controller);

        //obtengo el parametro del controlador.
        $propertie = $reflection->getProperty('view');

        //lo hago accesible para poderlo leer
        $propertie->setAccessible(true);
        $propertie->setValue($this->controller, $action);
    }

}