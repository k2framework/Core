<?php

namespace K2\View\Twig;

use \ArrayAccess;
use K2\Kernel\App;

class Globals implements ArrayAccess
{

    public function offsetExists($offset)
    {
        return in_array($offset, array('context', 'request', 'user', 'messages'));
    }

    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'context': return App::getContext();
            case 'request': return App::getRequest();
            case 'user': return App::getUser();
            case 'messages': return App::get('flash')->getAll();
        }
    }

    public function offsetSet($offset, $value)
    {
        //nada
    }

    public function offsetUnset($offset)
    {
        //nada
    }

}
