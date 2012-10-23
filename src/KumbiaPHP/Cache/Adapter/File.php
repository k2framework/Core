<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

namespace KumbiaPHP\Cache\Adapter;

use KumbiaPHP\Cache\Cache;
use KumbiaPHP\Kernel\Kernel;
use KumbiaPHP\Kernel\Request;
use KumbiaPHP\Kernel\Response;

/**
 * Cacheo de Archivos
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class File extends Cache
{

    /**
     * Obtiene el nombre de archivo a partir de un id y grupo
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    protected function getFilename($id, $group)
    {
        return $this->appPath . 'temp/cache/cache_' . md5($id) . '.' . md5($group);
    }

    /**
     * Carga un elemento cacheado
     *
     * @param string $id
     * @param string $group
     * @return Response
     */
    public function get($id, $group = 'default')
    {
        $filename = $this->getFilename($id, $group);
        if (file_exists($filename)) {
            $fh = fopen($filename, 'r');

            $lifetime = trim(fgets($fh));
            if ($lifetime == 'undefined' || $lifetime >= time()) {
                $data = stream_get_contents($fh);
                $response = unserialize($data);
            } else {
                $response = null;
            }

            fclose($fh);
            return $response;
        }
        return null;
    }

    /**
     * Guarda un elemento en la cache con nombre $id y valor $value
     *
     * @param string $id
     * @param string $group
     * @param string $value
     * @param int $lifetime tiempo de vida en forma timestamp de unix
     * @return boolean
     */
    public function save($id, Response $response)
    {
        $group = 'default';
        $lifetime = '+5 seconds';
        if ($lifetime) {
            $lifetime = strtotime($lifetime);
        } else {
            $lifetime = 'undefined';
        }

        $content = $lifetime . PHP_EOL . serialize($response);

        return file_put_contents($this->getFilename($id, $group), $content);
    }

    /**
     * Limpia la cache
     *
     * @param string $group
     * @return boolean
     */
    public function clean($group = false)
    {
        $pattern = $group ? $this->appPath . 'temp/cache/' . '*.' . md5($group) : $this->appPath . 'temp/cache/*';
        foreach (glob($pattern) as $filename) {
            if (!unlink($filename)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Elimina un elemento de la cache
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    public function remove($id, $group = 'default')
    {
        return unlink($this->getFilename($id, $group));
    }

}