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
     * @var mixed
     */
    protected $value = null;
    
     /**
     * Flag that something was changed in attribute
     * @var bool
     */
    protected $dirty = false;
    
    /**
     * @param array $config equals to {collectApp: web, section: sec, name: name, value: val}
     */
    public function __construct($config = array()) {
        $name = isset($config['name']) ? $config['name'] : null;
        $value = isset($config['value']) ? $config['value'] : null;
        $collectApp = isset($config['collectApp']) ? $config['collectApp'] : null;
        $section = isset($config['section']) ? $config['section'] : null;
        
        $this->setName($name);
        $this->setValue($value);
        $this->setCollectApp($collectApp);
        $this->setSection($section);
    }
    
    /**
     * Set attribute property and mark it as dirty
     * @param string $field Property to be set
     * @param mixed $value Property value
     * @return Attribute
     */
    protected function setField ($field, $value) {
        if ($this->{$field} !== $value) {
            $this->{$field} = $value;
            $this->setDirty();
        }
        return $this;
    }
    
    /**
     * Set attribute name
     * @param string $name
     * @return Attribute
     */
    public function setName ($name) {
        return $this->setField('name', $name);
    }

    /**
     * Set attribute application name
     * @param string $collectApp
     * @return Attribute
     */
    public function setCollectApp ($collectApp) {
        return $this->setField('collectApp', $collectApp);
    }

    /**
     * Set attribute section name
     * @param string $section
     * @return Attribute
     */
    public function setSection ($section) {
        return $this->setField('section', $section);
    }

    /**
     * Set attribute value
     * @param mixed value
     * @return Attribute
     */
    public function setValue ($value) {
        return $this->setField('value', $value);
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
     * TODO: json-schema validation
     * Check if attribute is valid (all required fields are present)
     * @return bool
     */
    public function isValid () {
        $value = $this->getValue();
        return !!($this->getName() && $this->getCollectApp() && $this->getSection() && $value !== null);
    }
    
    /**
     * Mark attribute as "dirty"
     * @return Attribute
     */
    protected function setDirty () {
        $this->dirty = true;
        return $this;
    }
    
    /**
     * Resets "dirty" status
     * @return Attribute
     */
    protected function resetDirty () {
        $this->dirty = false;
        return $this;
    }

    /**
     * Check if attribute has any changes
     * @return bool
     */
    public function hasChanges () {
        return !!$this->dirty;
    }    
}
