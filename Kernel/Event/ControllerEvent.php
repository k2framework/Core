<?php

namespace KumbiaPHP\Kernel\Event;

use KumbiaPHP\Kernel\Event\RequestEvent;
use KumbiaPHP\Kernel\Request;

/**
 * Description of ControllerEvent
 *
 * @author manuel
 */
class ControllerEvent extends RequestEvent
{

    protected $controller = array();

    function __construct(Request $request, array $controller = array())
    {
        parent::__construct($request);
        $this->controller = $controller;
    }

    /**
     *
     * @return array 
     */
    public function getController()
    {
        return $this->controller[0];
    }

    public function getAction()
    {
        return $this->controller[1];
    }

}