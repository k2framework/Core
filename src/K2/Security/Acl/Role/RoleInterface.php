<?php

namespace K2\Security\Acl\Role;

/**
 *
 * @author maguirre
 */
interface RoleInterface
{
    public function getName();
    public function getResources();
}
