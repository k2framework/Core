<?php

namespace KumbiaPHP\Security\Auth\Login;

use KumbiaPHP\Security\Config\Reader;
use KumbiaPHP\Security\Auth\Login\AbstractLogin;

/**
 * Description of Form
 *
 * @author manuel
 */
class Form extends AbstractLogin
{

    public function showLogin()
    {
        return $this->container->get('router')->redirect(Reader::get('security.login_url'));
    }

}