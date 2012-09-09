<?php

namespace KumbiaPHP\Security\Listener;

use KumbiaPHP\Security\Config\Reader;
use KumbiaPHP\Kernel\Event\RequestEvent;
use KumbiaPHP\Security\Auth\AuthManager;
use KumbiaPHP\Di\Container\ContainerInterface;

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
                return $this->loginCheck($event->getRequest());
            }
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

    protected function getProviderAndToken($provider)
    {
        $providerClassName = $this->container
                ->getParameter('security.provider.' . Reader::get('security.provider'));

        $tokenClassName = $this->container
                ->getParameter('security.token.' . Reader::get('security.provider'));

        if (!class_exists($providerClassName)) {
            die("No existe el proveedor $providerClassName");
        }
        if (!class_exists($tokenClassName)) {
            die("No existe el Token $tokenClassName");
        }

        return array(new $providerClassName(), new $tokenClassName($this->getUserObject()));
    }

    protected function loginCheck(\KumbiaPHP\Kernel\Request $request)
    {
        list ($provider, $token) = $this->getProviderAndToken(Reader::get('security.provider'));
        $auth = new AuthManager($provider);

        if ($auth->autenticate($token)) {
            $this->container->get('session')->set('token', $token, 'security');
            return $this->container->get('router')->redirect(Reader::get('security.target_login'));
        } else {
            return $this->showLogin();
        }
    }

    protected function showLogin()
    {
        $typeLoginClassName = 'KumbiaPHP\\Security\\Auth\\Login\\' . ucfirst(Reader::get('security.type'));
        if (!class_exists($typeLoginClassName)) {
            die("No existe el Tipo del Logueo $typeLoginClassName");
        }

        $login = new $typeLoginClassName($this->container);
        return $login->showLogin();
    }

    protected function logout()
    {
        $typeLoginClassName = 'KumbiaPHP\\Security\\Auth\\Login\\' . ucfirst(Reader::get('security.type'));

        if (!class_exists($typeLoginClassName)) {
            die("No existe el Tipo del Logueo $typeLoginClassName");
        }

        $login = new $typeLoginClassName($this->container);
        return $login->logout(Reader::get('security.target_logout'));
    }

    protected function getUserObject()
    {
        $request = $this->container->get('request');

        if ('active_record' === Reader::get('security.provider')) {
            $modelClassName = Reader::get('security.active_record.class');
            $usernameField = Reader::get('security.active_record.username') ? : 'username';

            if (!class_exists($modelClassName)) {
                die("No existe el proveedor $modelClassName");
            }

            $form = $request->get('form_login', array(
                $usernameField => $request->server->get('PHP_AUTH_USER'),
                'password' => $request->server->get('PHP_AUTH_PW'),
                    ));

            return new $modelClassName($form);
            ;
        } else {
            $form = $request->get('form_login', array(
                'username' => $request->server->get('PHP_AUTH_USER'),
                'password' => $request->server->get('PHP_AUTH_PW'),
                    ));
            return new \KumbiaPHP\Security\Auth\User\User($form);
        }
    }

}