<?php

namespace KumbiaPHP\Kernel\Event;

use KumbiaPHP\EventDispatcher\Event;
use KumbiaPHP\Kernel\Request;

/**
 * Description of RequestEvent
 *
 * @author manuel
 */
class RequestEvent extends Event
{

    /**
     *
     * @var Request 
     */
    protected $request;

    function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

}