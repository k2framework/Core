<?php

namespace K2\Security\Auth\Login;

use K2\Kernel\App;
use K2\Kernel\Config\Reader;
use K2\Security\Auth\Login\AbstractLogin;

/**
 * Description of Form
 *
 * @author manuel
 */
class Form extends AbstractLogin
{

    public function showLogin()
    {
        $currentUrl = App::getRequest()->getRequestUrl();
        $login_url = Reader::get('security.security.login_url');
        if ($currentUrl !== $login_url) {
            $this->container->get('session')
                    ->set('target_login', $currentUrl, 'security');
        }
        return $this->container->get('router')->redirect($login_url);
    }

}