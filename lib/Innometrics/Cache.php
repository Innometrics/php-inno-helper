<?php

namespace Innometrics;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Cache {
    
    /**
     * Cache storage
     * @var array
     */
    protected $cache = array();

    /**
     * Cache TTL
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
    }

    /**
     * Get data from cache by name if it's not expired
     * @param string $name
     * @return mixed
     */
    public function get ($name = '') {
        $value = null;
        if ($this->cachedTime && isset($this->cache[$name])) {
            if ($this->cache[$name]['expired'] <= microtime(true)) {
                unset($this->cache[$name]);
            } else {
                $value = $this->cache[$name]['value'];
            }
        }
        return $value;
    }

    /**
     * Set data to cache
     * @param string $name
     * @param mixed $value
     * @return undefined
     */
    public function set ($name, $value) {
        if ($this->cachedTime) {
            $this->cache[$name] = array(
                'expired' => microtime(true) + ($this->cachedTime * 1000),
                'value' => $value
            );
        }
    }

    /**
     * Expire record in cache by name
     * @param string $name
     * @return undefined
     */
    public function expire ($name) {
        if (isset($this->cache[$name])) {
            $this->cache[$name]['expired'] = 0;
        }
    }

    /**
     * Clear all cache records
     * @return undefined
     */
    public function clearCache () {
        $this->cache = array();
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
