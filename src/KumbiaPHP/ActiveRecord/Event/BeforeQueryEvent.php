<?php

namespace KumbiaPHP\ActiveRecord\Event;

use KumbiaPHP\EventDispatcher\Event;

/**
 * Description of BeforeEvent
 *
 * @author maguirre
 */
class BeforeQueryEvent extends Event
{

    protected $query;
    protected $parameters;

    public function __construct($query, $parameters = array())
    {
        $this->query = $query;
        $this->parameters = $parameters;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

}
