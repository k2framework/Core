<?php

namespace KumbiaPHP\Cache;

use KumbiaPHP\Cache\Cache;
use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Response;
use KumbiaPHP\Kernel\KernelInterface;

/**
 * Description of CacheKernel
 *
 * @author maguirre
 */
class CacheKernel implements KernelInterface
{

    /**
     *
     * @var KernelInterface 
     */
    protected $kernel;

    /**
     *
     * @var Cache 
     */
    protected $cache;

    function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->cache = Cache::driver($kernel);
    }

    public function execute(Request $request)
    {

        $id = md5($request->getRequestUrl());
        if (NULL !== $content = $this->cache->get($id)) {
            $content .= '<!-- Tiempo: ' . round(microtime(1) - START_TIME, 4) . ' seg. -->';
            $response = new Response($content);
        } else {
            $response = $this->kernel->execute($request);
            //$this->cache->save($response->getContent(), '+2 min', $id);
        }

        return $response;
    }

    public static function getContainer()
    {
        
    }

}
