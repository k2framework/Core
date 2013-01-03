<?php

namespace K2\Kernel\Event;

use K2\Kernel\Event\RequestEvent;
use K2\Kernel\Request;
use K2\Kernel\Response;

/**
 * Description of ResponseEvent
 *
 * @author manuel
 */
class ResponseEvent extends RequestEvent
{

    protected $response;

    function __construct(Request $request, Response $response)
    {
        parent::__construct($request);
        $this->response = $response;
    }

    /**
     *
     * @return Response 
     */
    public function getResponse()
    {
        return $this->response;
    }

}