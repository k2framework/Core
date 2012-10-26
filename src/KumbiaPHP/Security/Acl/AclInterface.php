<?php

namespace KumbiaPHP\Security\Acl;

use KumbiaPHP\Security\Acl\Role\RoleInterface;
use KumbiaPHP\Security\Auth\User\UserInterface;
use KumbiaPHP\Security\Acl\Resource\ResourceInterface;

/**
 *
 * @author maguirre
 */
interface AclInterface
{

    public function addResource(ResourceInterface $resource);

    public function setResources(array $resources);

    /**
     * Establece los recursos a los que el rol puede acceder
     *
     * @param RoleInterface $role nombre de rol
     * @param array $resources recursos a los que puede acceder el rol
     */
    public function allow(RoleInterface $role, $resourceName);

    /**
     * Establece los padres del rol
     *
     * @param RoleInterface $role rol
     * @param array $parents padres del rol
     */
    public function parents(RoleInterface $role, $parents);

    /**
     * Adiciona un usuario a la lista
     *
     * @param UserInterface $user
     */
    public function user(UserInterface $user);

    /**
     * Verifica si el usuario puede acceder al recurso
     * 
     * @param UserInterface $user usuario de la acl
     * @param ResourceInterface $resource recurso al cual se verificará acceso
     * @return boolean
     */
    public function check(UserInterface $user, ResourceInterface $resource);
}
