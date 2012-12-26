<?php

namespace K2\Security\Acl\Role;

use K2\Security\Acl\Role\RoleInterface;

/**
 * Description of Role
 *
 * @author maguirre
 */
class Role implements RoleInterface
{

    protected $name;

    function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getResources()
    {
        return array();
    }

}
