<?php

namespace K2\Kernel\Event;

use K2\Kernel\Request;
use K2\Kernel\Response;
use K2\Kernel\Event\RequestEvent;

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

    public function __construct(\Exception $e, Request $request)
    {
        parent::__construct($request);
        $this->exception = $e;
    }
    
    /**
     * Devuelve la Excepción que se disparó
     * @return \Exception 
     */
    public function getException()
    {
        return $this->exception;
    }
}