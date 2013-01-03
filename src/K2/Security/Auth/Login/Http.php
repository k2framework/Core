<?php

namespace K2\Security\Auth\Login;

use K2\Kernel\Response;
use K2\Security\Auth\Login\AbstractLogin;

/**
 * Description of Http
 *
 * @author manuel
 */
class Http extends AbstractLogin
{

    //put your code here
    public function showLogin()
    {
        return new Response(NULL, 401, array(
                    'WWW-Authenticate' => 'Basic realm=""',
                    'HTTP/1.0 401 Unauthorized',
                ));
    }

}