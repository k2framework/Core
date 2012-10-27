<?php

namespace KumbiaPHP\Security\Acl;

use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Security\Acl\Acl;
use KumbiaPHP\Security\Config\Reader;
use KumbiaPHP\Security\Exception\AclException;
use KumbiaPHP\Security\Acl\Role\RoleInterface;
use KumbiaPHP\Security\Auth\Token\TokenInterface;

/**
 * Description of AclManager
 *
 * @author manuel
 */
class AclManager
{

    /**
     * 
     * @var Acl 
     */
    protected static $acl;

    protected static function createPermisions(TokenInterface $token)
    {
        self::$acl = Acl::factory();

        $roles = $token->getRoles();

        foreach ($roles as $role) {
            if ($role instanceof RoleInterface) {
                $resources = $role->getResources();
                self::$acl->allow($role, $resources);
            }
        }
        self::$acl->user($token->getUser(), $roles);
    }

    public static function check(TokenInterface $token, Request $request)
    {
        if (!self::$acl) {
            self::createPermisions($token);
        }

        $module = trim($request->getAppContext()->getCurrentModule(), '/');
        $controller = $request->getAppContext()->getCurrentController();
        $action = $request->getAppContext()->getCurrentAction();

        return self::$acl->check($token->getUser(), "$module/*") ||
                self::$acl->check($token->getUser(), "$module/$controller/*") ||
                self::$acl->check($token->getUser(), "$module/$controller/$action");
    }

}