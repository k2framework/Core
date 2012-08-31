<?php

namespace KumbiaPHP\View\Helper;

use KumbiaPHP\Kernel\AppContext;

/**
 * Description of AbstractHelper
 *
 * @author manuel
 */
class AbstractHelper
{

    /**
     *
     * @var AppContext 
     */
    protected $app;

    /**
     * @Service(app.context,$app)
     * @param AppContext $app 
     */
    public function __construct(AppContext $app)
    {
        $this->app = $app;
    }

}