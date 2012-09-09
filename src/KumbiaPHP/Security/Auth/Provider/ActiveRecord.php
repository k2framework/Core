<?php

namespace KumbiaPHP\Security\Auth\Provider;

use KumbiaPHP\Security\Config\Reader;
use KumbiaPHP\Security\Auth\User\UserInterface;
use KumbiaPHP\Security\Auth\Token\TokenInterface;
use KumbiaPHP\Security\Auth\Provider\UserProviderInterface;

/**
 * Description of Model
 *
 * @author manuel
 */
class ActiveRecord implements UserProviderInterface
{

    /**
     *
     * @var UserInterface|ActiveRecord
     */
    protected $userModel;

    function __construct()
    {
        $ar = Reader::get('security.active_record');
        if ($ar && class_exists($ar['class'])) {
            $this->userModel = new $ar['class']();
        }else{
            die("No existe la clase para el activeRecord");
        }
    }

    //put your code here
    public function loadUser(TokenInterface $token)
    {
        $modelUsernameField = Reader::get('security.active_record.username');
        $user = $this->userModel->findBy($modelUsernameField, $token->getUsername());

        if (!$user instanceof UserInterface) {
            die("No existe {$token->getUsername()}");
            throw new \Exception("No existe");
        }
        return $user;
    }

}