<?php

namespace KumbiaPHP\Security\Acl\Adapter;

use KumbiaPHP\Security\Acl\Acl;
use KumbiaPHP\Security\Acl\Role\RoleInterface;
use KumbiaPHP\Security\Exception\AclException;
use KumbiaPHP\Security\Auth\User\UserInterface;
use KumbiaPHP\Security\Acl\Resource\ResourceInterface;

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
    protected $resources;

    public function allow(RoleInterface $role, $resourceName)
    {
        if (!$this->resourceExist($resourceName)) {
            throw new AclException("No se puede dar acceso al rol {$role->getName()} hacia el recurso inexistente $resourceName");
        }

        if (!isset($this->roles[$role->getName()]['resources'])) {
            $this->roles[$role->getName()]['resources'][] = $resourceName;
        } elseif (!in_array($resourceName, $this->roles[$role->getName()]['resources'])) {
            $this->roles[$role->getName()]['resources'][] = $resourceName;
        }
        return $this;
    }

    public function check(UserInterface $user, ResourceInterface $resource)
    {
        foreach ((array)$user->getRoles() as $role) {
            if ($this->isAllowed($role, $resource)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function parents(RoleInterface $role, $parents)
    {
        $this->roles[$role->getName()]['parents'] = $parents;
    }

    public function user(UserInterface $user)
    {
        $this->users[$user->getUsername()] = $user->getRoles();
    }

    public function addResource(ResourceInterface $resource)
    {
        $this->resources[$resource->getName()] = $resource;
    }

    public function setResources(array $resources)
    {
        $this->resources = array();
        foreach ($resources as $resource) {
            $this->resources[$resource->getName()] = $resource;
        }
    }

    protected function resourceExist($name)
    {
        return isset($this->resources[$name]);
    }

    protected function isAllowed(RoleInterface $role, ResourceInterface $resource)
    {
        if (in_array($resource->getName(), $this->roles[$role->getName()]['resources'])) {
            return TRUE;
        }
        foreach ((array) $this->roles[$role->getName()]['parents'] as $parent) {
            if ($this->isAllowed($parent, $resource)) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
