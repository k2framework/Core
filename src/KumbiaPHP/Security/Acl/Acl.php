<?php

namespace KumbiaPHP\Security\Acl;

use KumbiaPHP\Security\Acl\AclInterface;
use KumbiaPHP\Security\Acl\Role\RoleInterface;
use KumbiaPHP\Security\Exception\AclException;
use KumbiaPHP\Security\Acl\Resource\ResourceInterface;

/**
 * Clase Base para gestión de ACL
 *
 * Nueva Clase Base para gestión de ACL (Access Control List) permisos
 *
 * @category   Kumbia
 * @package    Acl
 */
abstract class Acl implements AclInterface {

    /**
     *
     * @param string $adapter
     * @return AclInterface 
     */
    public static function factory($adapter = 'simple') {
        return new Adapter\Simple();
    }
    
    protected function getRole($role) {
        if ($role instanceof RoleInterface) {
            $role = $role->getName();
        } elseif (!is_string($role) && !is_int($role)) {
            throw new AclException('el parametro $role debe ser una cadena ó un objeto de tipo RoleInterface');
        }
        return $role;
    }

    protected function getResource($resource) {
        if ($resource instanceof ResourceInterface) {
            $resource = $resource->getName();
        } elseif (!is_string($resource) && !is_int($resource)) {
            throw new AclException('el parametro $resource debe ser una cadena ó un objeto de tipo RoleInterface');
        }
        return $resource;
    }

}
