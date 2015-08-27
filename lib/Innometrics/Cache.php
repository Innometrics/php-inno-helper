<?php

namespace Innometrics;

use Illuminate\Cache\CacheManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Container\Container;
 
/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Cache {
    
    /**
     * Cache storage
     * @var array
     */
    protected $cache = null;

    /**
     * Cache TTL in seconds
     * @var integer
     */
    protected $cachedTime = 60;
    
    /**
     * @param array $config
     */
    public function __construct($config = array()) {
        if (!is_array($config)) {
            throw new \ErrorException('Config should be an object');
        }

        if (isset($config['cachedTime'])) {
            $cacheTime = $config['cachedTime'];
            $this->cachedTime = is_numeric($cacheTime) && $cacheTime > 0 ? $cacheTime : 60;
        }
        
        $app = new Container();
        $app->singleton('files', function() {
            return new Filesystem();
        });
        $app->singleton('config', function() {
            return array(
                'cache.default' => 'file',
                'cache.stores.file' => array(
                    'driver' => 'file',
                    'path' => 'cache'
                )
            );
        });

        $cacheManager = new CacheManager($app);        
        $this->cache = $cacheManager->driver();
    }

    /**
     * Get data from cache by name if it's not expired
     * @param string $name
     * @return mixed
     */
    public function get ($name = '') {
        return $this->cache->get($name);
    }

    /**
     * Set data to cache
     * @param string $name
     * @param mixed $value
     * @return undefined
     */
    public function set ($name, $value) {
        return $this->cache->put($name, $value, $this->cachedTime / 60);
    }

    /**
     * Expire record in cache by name
     * @param string $name
     * @return undefined
     */
    public function expire ($name) {
        $this->cache->forget($name);
    }

    /**
     * Clear all cache records
     * @return undefined
     */
    public function clearCache () {
        $this->cache->flush();
    }

    /**
     * Change cache TTL
     * @param integer $time
     * @returns {undefined}
     */
    public function setCachedTime ($time) {
        $this->cachedTime = $time;
    }
    
}
