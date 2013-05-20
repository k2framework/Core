<?php

namespace K2\Security\Auth\Provider;

use K2\Kernel\App;
use K2\Security\Config\Reader;
use K2\Security\Auth\User\UserInterface;
use K2\Security\Exception\AuthException;
use K2\Security\Auth\Token\TokenInterface;
use K2\Security\Auth\Provider\AbstractProvider;
use K2\Security\Exception\UserNotFoundException;
use K2\Security\Auth\Token\ActiveRecord as Token;

/**
 * Description of Model
 *
 * @author manuel
 */
class ActiveRecord extends AbstractProvider
{

    protected $config;

    //put your code here
    public function loadUser(TokenInterface $token)
    {
        $user = $token->getUser();
        $user = $user::findBy($this->config['username'], $token->getUsername());

        if (!$user instanceof UserInterface) {
            throw new UserNotFoundException("No existe el Usuario {$token->getUsername()} en la Base de Datos");
        }
        return $user;
    }

    public function getToken(array $config = array(), array $data = null)
    {
        $this->config = $config;

        $request = App::getRequest();

        /**
         * Si data es diferente de nulo, se usa data, sino se busca en request 
         */
        $form = $data ? : $request->request('login', array(
                    $config['username'] => $request->server('PHP_AUTH_USER'),
                    'password' => $request->server('PHP_AUTH_PW'),
                ));

        if (!isset($config['class'])) {
            throw new AuthException("Debe definir un valor para el índice user[class] en el security.ini");
        }

        if (!isset($config['username'])) {
            throw new AuthException("Debe definir un valor para el índice user[username] en el security.ini");
        }

        if (!class_exists($config['class'])) {
            throw new AuthException("No existe la clase de usuario '{$config['class']}'<br/>en el security.ini en la seccion  '[model_config]'");
        }

        $user = new $config['class']($form);

        if (!($user instanceof \K2\ActiveRecord\ActiveRecord)) {
            throw new AuthException("La clase {$config['class']} debe extender de ActiveRecord");
        }

        if (!($user instanceof UserInterface)) {
            throw new AuthException("La clase {$config['class']} debe implementar la interface de UserInterface");
        }

        return new Token($user);
    }

}