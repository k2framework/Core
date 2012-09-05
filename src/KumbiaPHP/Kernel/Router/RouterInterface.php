<?php

namespace KumbiaPHP\Kernel\Router;

/**
 * Description of RouterInterface
 *
 * @author manuel
 */
interface RouterInterface
{

    /**
     * @param string $url
     * @return \KumbiaPHP\Kernel\RedirectResponse 
     */
    public function redirect($url = NULL);

    /**
     * @param string $action
     * @return \KumbiaPHP\Kernel\RedirectResponse 
     */
    public function toAction($action);
    
    /**
     * @param string $action
     * @return \KumbiaPHP\Kernel\Response 
     */
    public function forward($url);
}