<?php

namespace Innometrics;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Profile {

    /**
     * Profile id
     * @var string
     */
    protected $id = null;

    /**
     * @param array $config Initial config variables
     */
    public function __construct($config = array()) {
        $this->id = isset($config['id']) ? $config['id'] : 'TODO:idGenerator.generate(32)';
        
        $this->initAttributes(isset($config['attributes']) ? $config['attributes'] : array());
        $this->initSessions(isset($config['sessions']) ? $config['sessions'] : array());
    }
    
    /**
     * Get application name
     * @return string
     */
    public function getId () {
        return $this->id;
    }
    
    /**
     * Create attributes by initial data
     * @param array $rawAttributesData
     * @return Profile
     */
    protected function initAttributes ($rawAttributesData) {
        
    }
   
    /**
     * Create session by initial data
     * @param array $rawSessionsData
     * @return Profile
     */
    protected function initSessions ($rawSessionsData) {
        
    }

}
