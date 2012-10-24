<?php

namespace KumbiaPHP\Kernel\Event;

use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Response;
use KumbiaPHP\Kernel\Event\RequestEvent;

/**
 * Description of RequestEvent
 *
 * @author manuel
 */
class ExceptionEvent extends RequestEvent
{
    /**
     *
     * @var \Exception 
     */
    protected $exception;

    function __construct(\Exception $e, Request $request)
    {
        $this->request = $request;
    }

}