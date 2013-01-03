<?php

namespace K2\Security\Acl\Resource;

use K2\Security\Acl\Resource\ResourceInterface;

/**
 * Description of Resource
 *
 * @author maguirre
 */
class Resource implements ResourceInterface
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

}
