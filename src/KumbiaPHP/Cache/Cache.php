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
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

namespace KumbiaPHP\Cache;

use KumbiaPHP\Kernel\Kernel;
use KumbiaPHP\Kernel\Response;
use KumbiaPHP\Cache\CacheException;

/**
 * Clase base para componentes de cacheo
 *
 * @category   Kumbia
 * @package    Cache
 */
abstract class Cache
{

    /**
     * Pool de drivers para cache
     *
     * @var array
     * */
    protected static $drivers = array();

    /**
     * Tiempo de vida
     *
     * @var string
     */
    protected $lifetime = null;

    /**
     *
     * @var string 
     */
    protected $appPath;

    function __construct($appPath)
    {
        $this->appPath = $appPath;
    }

    /**
     * Carga un elemento cacheado
     *
     * @param string $id
     * @param string $group
     * @return Response
     */
    public abstract function get($id, $group = 'default');

    /**
     * Guarda un elemento en la cache con nombre $id y valor $value
     *
     * @param string $value
     * @param string $lifetime tiempo de vida con formato strtotime, utilizado para cache
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public abstract function save($id, Response $response);

    /**
     * Limpia la cache
     *
     * @param string $group
     * @return boolean
     */
    public abstract function clean($group = false);

    /**
     * Elimina un elemento de la cache
     *
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public abstract function remove($id, $group = 'default');

    /**
     * Obtiene el Adaptador de cache indicado
     *
     * @param Kernel $kernel 
     * */
    public static function factory(Kernel $kernel)
    {
        if (Kernel::getContainer()->hasParameter('cache')) {
            $driver = Kernel::getContainer()->getParameter('cache');
        } else {
            $driver = 'file';
        }

        if (!isset(self::$drivers[$driver])) {
            $class = 'KumbiaPHP\Cache\Adapter\\' . ucfirst($driver);

            if (!class_exists($class)) {
                throw new CacheException("No existe el Adaptador de Cache \"$class\"");
            }

            self::$drivers[$driver] = new $class($kernel->getAppPath());
        }

        return self::$drivers[$driver];
    }

}
