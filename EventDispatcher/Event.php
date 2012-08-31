<?php

namespace KumbiaPHP\EventDispatcher;

/**
 * Description of Event
 *
 * @author manuel
 */
class Event
{

    protected $propagationStopped = FALSE;

    public function stopPropagation()
    {
        $this->propagationStopped = TRUE;
    }

    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

}