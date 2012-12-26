<?php

namespace K2\Security\Event;

use K2\Kernel\Request;
use K2\Kernel\Event\RequestEvent;
use K2\Security\Security;

class SecurityEvent extends RequestEvent
{

    /**
     *
     * @var Security 
     */
    protected $security;

    function __construct(Request $request, Security $security)
    {
        parent::__construct($request);
        $this->security = $security;
    }

    /**
     * @return Security
     */
    public function getSecutiry()
    {
        return $this->security;
    }

}
