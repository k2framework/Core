<?php

namespace K2\Security\Listener;

use K2\Security\Security;
use K2\Kernel\Config\Reader;
use K2\Security\Event\Events;
use K2\Security\Acl\AclManager;
use K2\Kernel\Event\RequestEvent;
use K2\Security\Auth\AuthManager;
use K2\Security\Event\SecurityEvent;
use K2\Di\Container\Container;
use K2\Security\Exception\AuthException;
use K2\Security\Exception\UserNotFoundException;
use K2\Security\Exception\UserNotAuthorizedException;

/**
 * Description of Firewall
 *
 * @author manuel
 */
class Firewall
{

    /**
     *
     * @var Container
     */
    protected $container;

    function __construct(Container $container)
    {
        $this->container = $container;
        Reader::read('security');
    }

    /**
     * metodo que será llamado por el dispatcher en el evento kumbia.request
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $router = $this->container->get('router');

        $url = trim($event->getRequest()->getRequestUrl(), '/');

        $loginUrl = $router->createUrl(Reader::get('security.security.login_url'), false);
        //verificamos la existencia del token en la session.
        if (!$this->container->get('security')->isLogged()) {
            if ($url === $loginUrl && !$event->getRequest()->isMethod('post')) {
                //si no existe el token y la url es la del logueo, nos vamos.
                return;
            } elseif ((($this->isSecure($url) || $url === '_autenticate') && $event->getRequest()->isMethod('post')) ||
                    ('http' === Reader::get('security.security.type') &&
                    $event->getRequest()->server->get('PHP_AUTH_USER') &&
                    $event->getRequest()->server->get('PHP_AUTH_PW'))
            ) {
                //si estamos verificando los datos de acceso.
                $event->stopPropagation();
                $event->setResponse($this->loginCheck());
                return;
            }
        } elseif ($url === $loginUrl || $url === '_autenticate') {
            //si ya existe el token y estamos en la url del form de logueo, mandamos al target_login
            $event->stopPropagation();
            $event->setResponse($router->redirect(Reader::get('security.security.target_login')));
            return;
        }

        if ('logout' === $url) {
            //Si estoy en la pagina de logout, realizo el cierre de sesión.
            $event->stopPropagation();
            $event->setResponse($this->logout());
            return;
        }

        if ($this->isSecure($url)) { //si la url es segura
            if ($this->container->get('security')->isLogged()) {
                $token = $this->container->get('security')->getToken();
                //si hay definidos modelos para el ACL
                if (!AclManager::check($token, $event->getRequest())) {
                    $user = $token->getUsername();
                    $uri = $event->getRequest()->getRequestUrl();
                    throw new UserNotAuthorizedException("el usuario \"$user\" no tiene permisos para acceder al recurso \"$url\"");
                }
                return;
            }
            //si aun no está logueado
            $event->stopPropagation();
            $event->setResponse($this->showLogin());
        }
    }

    /**
     * Verifica si la URL actual está protegida.
     * @param string $url
     * @return boolean 
     */
    protected function isSecure($url)
    {
        $routes = (array) Reader::get('security.routes');
        $url = '/' . ltrim($url, '/');
        if (isset($routes[$url])) {
            return $routes[$url];
        }
        foreach ($routes as $route => $roles) {
            $route = str_replace('*', '', $route);
            if (0 === strpos($url, $route)) {
                return $roles;
            }
        }
        return FALSE;
    }

    /**
     * Devuelve un arreglo con las instancias del proveedor de usuarios
     * y el token.
     * @param string $provider
     * @param array|null $data Si se pasa $data se usará para obtener los datos con los
     * que se cargará el token y el usuario para el chequeo.
     * @return array
     * @throws AuthException 
     */
    protected function getProviderAndToken($provider, array $data = null)
    {
        if (0 === strpos($provider, '@')) {
            $provider = $this->container->get(str_replace('@', '', $provider));
        } else {
            $config = $this->container->getParameter('security');

            if (!isset($config['provider'][$provider])) {
                throw new AuthException("No existe el proveedor $provider");
            }

            $providerClassName = $config['provider'][$provider];

            if (!class_exists($providerClassName)) {
                $providerClassName || $providerClassName = $provider;
                throw new AuthException("No existe el proveedor $providerClassName");
            }
            $provider = new $providerClassName($this->container);
        }

        if (!($provider instanceof \K2\Security\Auth\Provider\UserProviderInterface )) {
            throw new AuthException("la clase proveedora de usuarios debe implementar la interface UserProviderInterface");
        }

        return array($provider, $provider->getToken((array) Reader::get('security.model_config.user'), $data));
    }

    /**
     * Efectua el proceso de logueo.
     * @param array $data
     * @return Response devuelve una redireción hacia la ruta deseada, si se logueó correctamente,
     * ó una redireción al formulario de logueo.
     */
    public function loginCheck(array $data = null)
    {
        try {
            list ($provider, $token) = $this->getProviderAndToken(Reader::get('security.security.provider'), $data);

            $auth = new AuthManager($provider);

            $auth->autenticate($token); //si falla el logueo, tira una excepcion.

            $this->container->get('session')->set('token', $token, 'security');

            $event = new SecurityEvent(\K2\Kernel\App::getRequest()
                    , $this->container->get('security'));

            $this->container->get('event.dispatcher')->dispatch(Events::LOGIN, $event);

            if ($event->hasResponse()) {//si se estableció una respuesta
                //eliminamos la sesión por si se creó
                $this->container->get('session')->delete('target_login', 'security');
                return $event->getResponse();
            }

            if ($url = $this->container->get('session')->get('target_login', 'security')) {
                $this->container->get('session')->delete('target_login', 'security');
                return $this->container->get('router')->redirect($url);
            } else {
                return $this->container->get('router')->redirect(Reader::get('security.security.target_login'));
            }
        } catch (UserNotFoundException $e) {
            $this->container->get('session')->set(Security::LOGIN_ERROR, "Usuario ó Contraseña Invalidos");
        }
        return $this->showLogin();
    }

    /**
     * Muestra el formulario de login.
     * @return \K2\Kernel\Response
     * @throws AuthException 
     */
    public function showLogin()
    {
        $typeLoginClassName = 'K2\\Security\\Auth\\Login\\' . ucfirst(Reader::get('security.security.type'));
        if (!class_exists($typeLoginClassName)) {
            throw new AuthException("No existe el Tipo del Logueo $typeLoginClassName");
        }

        $login = new $typeLoginClassName($this->container);
        return $login->showLogin();
    }

    /**
     * Desloguea al usuario del sistema.
     * @return \K2\Kernel\Response 
     */
    public function logout()
    {
        $typeLoginClassName = 'K2\\Security\\Auth\\Login\\' . ucfirst(Reader::get('security.security.type'));

        if (!class_exists($typeLoginClassName)) {
            die("No existe el Tipo del Logueo $typeLoginClassName");
        }

        $event = new SecurityEvent(\K2\Kernel\App::getRequest()
                , $this->container->get('security'));

        $this->container->get('event.dispatcher')->dispatch(Events::LOGOUT, $event);

        //eliminamos la sesión
        $this->container->get('session')->delete(null, 'security');

        if ($event->hasResponse()) {//si se estableció una respuesta
            return $event->getResponse();
        }

        return $this->container->get('router')
                        ->redirect(Reader::get('security.security.target_logout'));
    }

}