<?php

namespace KumbiaPHP\Security\Acl\Adapter;

use KumbiaPHP\Security\Acl\Acl;
use KumbiaPHP\Security\Exception\AclException;
use KumbiaPHP\Security\Auth\User\UserInterface;

/**
 * Implementacion de ACL con definicion de reglas en PHP
 *
 * @category   Kumbia
 * @package    Acl
 */
class Simple extends Acl
{

    protected $roles;
    protected $users;

    public function allow($role, array $resources = array())
    {
        foreach ($resources as $resource) {
            $this->roles[$this->getRole($role)]['resources'][] = $this->getResource($resource);
        }

        return $this;
    }

    public function check(UserInterface $user, $resource)
    {
        $roles = $user->getRoles();

        foreach ((array) $roles as $role) {
            if ($this->isAllowed($role, $resource)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function parents($role, array $parents = array())
    {
        $this->roles[$this->getRole($role)]['parents'] = $parents;
        return $this;
    }

    public function user(UserInterface $user, array $roles = array())
    {
        $this->users[$user->getUsername()] = count($roles) ? $roles : $user->getRoles();
        return $this;
    }

    protected function isAllowed($role, $resource)
    {

        $role = $this->getRole($role);
        $resource = $this->getResource($resource);

        if (!isset($this->roles[$role])) {
            return FALSE;
        }

        if (in_array($resource, $this->roles[$role]['resources'])) {
            return TRUE;
        }

        if (!isset($this->roles[$role]['parents'])) {
            return FALSE;
        }

        foreach ((array) $this->roles[$role]['parents'] as $parent) {
            if ($this->isAllowed($parent, $resource)) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
