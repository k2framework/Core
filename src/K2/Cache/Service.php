<?php

namespace K2\Cache;

use K2\Cache\Cache;
use K2\Kernel\AppContext;

/**
 * Description of Service
 *
 * @author manuel
 */
class Service
{

    protected $appContext;

    function __construct(AppContext $app)
    {
        $this->appContext = $app;
    }

    public function get()
    {
        return \K2\Cache\Cache::driver($this->appContext);
    }

}