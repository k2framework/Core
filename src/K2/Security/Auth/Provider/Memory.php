<?php

namespace K2\Security\Auth\Provider;

use K2\Kernel\App;
use K2\Kernel\Config\Reader;
use K2\Security\Auth\User\Memory as User;
use K2\Security\Auth\Token\User as Token;
use K2\Security\Auth\Token\TokenInterface;
use K2\Security\Auth\Provider\AbstractProvider;
use K2\Security\Exception\UserNotFoundException;

/**
 * Description of Memory
 *
 * @author manuel
 */
class Memory extends AbstractProvider
{

    //put your code here
    public function loadUser(TokenInterface $token)
    {
        $users = (array) Reader::get('security.users');

        if (!isset($users[$token->getUsername()])) {
            throw new UserNotFoundException("No existe el Usuario {$token->getUsername()} en la Base de Datos");
        }

        $data['username'] = $token->getUsername();
        $data['password'] = key($users[$token->getUsername()]);
        $data['roles'] = explode(',', $users[$token->getUsername()][$data['password']]);

        $userClass = get_class($token->getUser());

        return new $userClass($data);
    }

    public function getToken(array $config = array(), array $data = null)
    {
        $this->config = $config;

        isset($config['username']) || $config['username'] = 'username';
        isset($config['password']) || $config['password'] = 'password';

        $request = App::getRequest();

        /**
         * Si data es diferente de nulo, se usa data, sino se busca en request 
         */
        $form = $data ? : $request->post('form_login', array(
                    $config['username'] => $request->server('PHP_AUTH_USER'),
                    'password' => $request->server('PHP_AUTH_PW'),
                ));

        $form['username'] = $form[$config['username']];
        $form['password'] = $form[$config['password']];

        $user = new User($form); //por ahora siempre usaran las clase de usuario Memory

        return new Token($user);
    }

}