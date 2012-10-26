<?php

namespace KumbiaPHP\Security\Acl;

use KumbiaPHP\Security\Acl\AclInterface;

/**
 * Clase Base para gestión de ACL
 *
 * Nueva Clase Base para gestión de ACL (Access Control List) permisos
 *
 * @category   Kumbia
 * @package    Acl
 */
abstract class Acl implements AclInterface
{

    /**
     *
     * @param string $adapter
     * @return AclInterface 
     */
    public static function factory($adapter = 'simple')
    {
        return new Adapter\Simple();
    }

}
