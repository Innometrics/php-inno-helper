<?php

namespace Innometrics;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Attribute {

    /**
     * Attribute name
     * @var string
     */
    protected $name = null;

    /**
     * Attribute application name
     * @var string
     */
    protected $collectApp = null;

    /**
     * Attribute section name
     * @var string
     */
    protected $section = null;

    /**
     * Attribute value
     * @var string
     */
    protected $value = null;
    
    /**
     * @param array $config equals to {collectApp: web, section: sec, name: name, value: val}
     */
    public function __construct($config = array()) {
        $fields = array('name', 'value', 'section', 'collectApp');
        foreach ($fields as $field) {
            if (isset($config[$field])) {
                $this->{$field} = $config[$field];
            }
        }
    }
    
    /**
     * Set attribute name
     * @param string $name
     * @return Attribute
     */
    public function setName ($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Set attribute application name
     * @param string $collectApp
     * @returns Attribute
     */
    public function setCollectApp ($collectApp) {
        $this->collectApp = $collectApp;
        return $this;
    }

    /**
     * Set attribute section name
     * @param string $section
     * @return Attribute
     */
    public function setSection ($section) {
        $this->section = $section;
        return $this;
    }

    /**
     * Set attribute value
     * @param mixed value
     * @return Attribute
     */
    public function setValue ($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * Get attribute name
     * @return string
     */
    public function getName () {
        return $this->name;
    }

    /**
     * Get attribute application name
     * @return string
     */
    public function getCollectApp () {
        return $this->collectApp;
    }

    /**
     * Get attribute section name
     * @return string
     */
    public function getSection () {
        return $this->section;
    }

    /**
     * Get attribute value
     * @return mixed
     */
    public function getValue () {
        return $this->value;
    }

    /**
     * Check if attribute is valid (all required fields are present)
     * @returns {boolean}
     */
    public function isValid () {
        $value = $this->getValue();
        return !!($this->getName() && $this->getCollectApp() && $this->getSection() && $value !== null);
    }
}


