<?php

namespace Innometrics;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Segment {

    /**
     * Segment id
     * @var string
     */
    protected $id = null;

    /**
     * Segment IQL
     * @var string
     */
    protected $iql = null;

    /**
     * @param array $config Initial config variables
     */
    public function __construct($config = array()) {
        $this->validateConfig($config);
        $this->id = isset($config['id']) ? $config['id'] : '';
        $this->iql = isset($config['iql']) ? $config['iql'] : '';        
    }
    
    /**
     * Get segment id
     * @return string
     */
    public function getId () {
        return $this->id;
    }
    
    /**
     * Get segment IQL expression
     * @return string
     */
    public function getIql () {
        return $this->iql;
    }
    
    /**
     * Check if segment is valid
     * @return bool
     */
    public function isValid () {
        return !!($this->getId() && $this->getIql());
    }
    
    /**
     * Checks if config is valid
     * @throws \ErrorException If config are not suitable exception will be thrown
     */
    protected function validateConfig ($config = array()) {
        if (!is_array($config) || !count($config)) {
            throw new \ErrorException('Config should be a non-empty array');
        }

        $fields = array('id', 'iql');
        foreach ($fields as $field) {
            if (!array_key_exists($field, $config)) {
                throw new \ErrorException('Property "' . $field . '" in config should be defined');
            }
            if (gettype($config[$field]) !== 'string') {
                throw new \ErrorException('Property "' . $field . '" in config should be a string');
            }
            if (!trim($config[$field])) {
                throw new \ErrorException('Property "' . $field . '" in config can not be empty');
            }
        }
    }

}
