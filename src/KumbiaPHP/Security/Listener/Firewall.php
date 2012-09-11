<?php

namespace KumbiaPHP\Security\Listener;

use KumbiaPHP\Security\Config\Reader;
use KumbiaPHP\Kernel\Event\RequestEvent;
use KumbiaPHP\Security\Auth\AuthManager;
use KumbiaPHP\Di\Container\ContainerInterface;
use KumbiaPHP\Security\Exception\AuthException;
use KumbiaPHP\Security\Exception\UserNotFoundException;

/**
 * Description of Firewall
 *
 * @author manuel
 */
class Firewall
{

    /**
     *
     * @var ContainerInterface
     */
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * metodo que será llamado por el dispatcher en el evento kumbia.request
     * @param RequestEvent $event
     * @return NULL|\KumbiaPHP\Kernel\Response 
     */
    public function onKernelRequest(RequestEvent $event)
    {
        Reader::readSecurityConfig($this->container->get('app.context'));


        $url = $event->getRequest()->getRequestUrl();
        if (!$logged = $this->container->get('session')->has('token', 'security')) {
            if ($url === Reader::get('security.login_url')) {//
                return;
            } elseif ($url === '/_autenticate' ||
                    ('http' === Reader::get('security.type') &&
                    $event->getRequest()->server->get('PHP_AUTH_USER') &&
                    $event->getRequest()->server->get('PHP_AUTH_PW'))) {
                $event->stopPropagation();
                return $this->loginCheck();
            }
        } elseif ($url == Reader::get('security.login_url') || $url === '/_autenticate') {
            $event->stopPropagation();
            return $this->container->get('router')
                            ->redirect(Reader::get('security.target_login'));
        }

        if ($url === Reader::get('security.logout_url')) {//
            $event->stopPropagation();
            return $this->logout();
        }

        if ($roles = $this->isSecure($url)) { //si la url es segura
            if ($logged && $this->container->get('session')
                            ->get('token', 'security')->isValid()) {
                return;
            }
            //si aun no está logueado
            $event->stopPropagation();
            return $this->showLogin();
        }
    }

    /**
     * Verifica si la URL actual está protegida.
     * @param string $url
     * @return boolean 
     */
    protected function isSecure($url)
    {
        $routes = (array) Reader::get('routes');
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
     * @return array
     * @throws AuthException 
     */
    protected function getProviderAndToken($provider)
    {
        if (0 === strpos($provider, '@')) {
            $provider = $this->container->get(str_replace('@', '', $provider));
        } else {
            $providerClassName = $this->container
                    ->getParameter('security.provider.' . $provider);

            if (!class_exists($providerClassName)) {
                $providerClassName || $providerClassName = $provider;
                throw new AuthException("No existe el proveedor $providerClassName");
            }
            $provider = new $providerClassName($this->container);
        }

        if (!($provider instanceof \KumbiaPHP\Security\Auth\Provider\UserProviderInterface )) {
            throw new AuthException("la clase proveedora de usuarios debe implementar la interface UserProviderInterface");
        }

        return array($provider, $provider->getToken((array) Reader::get('security.user')));
    }

    protected function loginCheck()
    {
        try {
            list ($provider, $token) = $this->getProviderAndToken(Reader::get('security.provider'));
            $auth = new AuthManager($provider);

            $auth->autenticate($token);

            $this->container->get('session')->set('token', $token, 'security');
            if ($url = $this->container->get('session')->get('target_login', 'security')) {
                $this->container->get('session')->delete('target_login', 'security');
                return $this->container->get('router')->redirect($url);
            } else {
                return $this->container->get('router')->redirect(Reader::get('security.target_login'));
            }
        } catch (UserNotFoundException $e) {
            $this->container->get('flash')->set("LOGIN_ERROR", "Usuario ó Contraseña Invalidos");
        }
        return $this->showLogin();
    }

    /**
     * Muestra el formulario de login.
     * @return \KumbiaPHP\Kernel\Response
     * @throws AuthException 
     */
    protected function showLogin()
    {
        $typeLoginClassName = 'KumbiaPHP\\Security\\Auth\\Login\\' . ucfirst(Reader::get('security.type'));
        if (!class_exists($typeLoginClassName)) {
            throw new AuthException("No existe el Tipo del Logueo $typeLoginClassName");
        }

        $login = new $typeLoginClassName($this->container);
        return $login->showLogin();
    }

    /**
     * Desloguea al usuario del sistema.
     * @return \KumbiaPHP\Kernel\Response 
     */
    protected function logout()
    {
        $typeLoginClassName = 'KumbiaPHP\\Security\\Auth\\Login\\' . ucfirst(Reader::get('security.type'));

        if (!class_exists($typeLoginClassName)) {
            die("No existe el Tipo del Logueo $typeLoginClassName");
        }

        $login = new $typeLoginClassName($this->container);
        return $login->logout(Reader::get('security.target_logout'));
    }

}