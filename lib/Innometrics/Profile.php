<?php

namespace Innometrics;

use Innometrics\Attribute;
use Innometrics\Session;
use Innometrics\IdGenerator;

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
     * Profile attributes
     * @var array
     */
    protected $attributes = array();

    /**
     * Profile sessions
     * @var array
     */
    protected $sessions = array();

    /**
     * @param array $config Initial config variables
     */
    public function __construct($config = array()) {
        $this->id = isset($config['id']) && $config['id'] ? $config['id'] : IdGenerator::generate(32);
        
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
     * Create attributes by application, section and data object
     * @param string $collectApp
     * @param string $section
     * @param array $attributesData
     * @return Attribute[]
     */
    public function createAttributes ($collectApp, $section, $attributesData) {
        if (!$collectApp || !$section) {
            throw new \ErrorException('collectApp and section should be filled to create attribute correctly');
        }

        if (!$attributesData || !is_array($attributesData)) {
            throw new \ErrorException('attributes should be an array');
        }

        $names = array_keys($attributesData);

        if (!count($names)) {
            throw new \ErrorException('attributes are empty');
        }

        $attrs = array();
        foreach ($names as $name) {
            $attrs[] = $this->createAttribute(
                $collectApp,
                $section,
                $name,
                $attributesData[$name]
            );
        }
        
        return $attrs;
    }

    /**
     * Create attribute
     * @param string $collectApp
     * @param string $section
     * @param string $name
     * @param mixed $value
     * @return Attribute
     */
    public function createAttribute ($collectApp, $section, $name, $value) {
        return new Attribute(array(
            'collectApp' => $collectApp,
            'section' => $section,
            'name' => $name,
            'value' => $value
        ));
    }

    /**
     * Get attributes. Can be filtered by application or section.
     * @param string $collectApp
     * @param string $section
     * @return Attribute[]
     */
    public function getAttributes ($collectApp = null, $section = null) {
        $attributes = $this->attributes;
        $filters = array();

        if ($collectApp) {
            $filters[] = function ($attribute) use ($collectApp) {
                return $attribute->getCollectApp() === $collectApp;
            };
        }
        
        if ($section) {
            $filters[] = function ($attribute) use ($section) {
                return $attribute->getSection() === $section;
            };
        }
        
        if (count($filters)) {
            foreach ($filters as $filter) {
                $attributes = array_filter($attributes, $filter);            
            }
        }

        return $attributes;
    }

    /**
     * Get attribute by name, application and section
     * @param string $name
     * @param string $collectApp
     * @param string $section
     * @return Attribute|null
     */
    public function getAttribute ($name, $collectApp, $section) {
        if (!$name || !$collectApp || !$section) {
            throw new \ErrorException('Name, collectApp and section should be filled to get attribute');
        }
        
        $attributes = $this->getAttributes($collectApp, $section);
        $attributes = array_filter($attributes, function ($attr) use ($name) {
            return $attr->getName() === $name;
        });
        
        $keys = array_keys($attributes);
        return count($keys) ? $attributes[$keys[0]] : null;        
    }

    /**
     * Add attribute to profile or update existing
     * @param Attribute|array $attribute
     * @return Profile
     */
    public function setAttribute ($attribute) {
        $this->setAttributes(array($attribute));
        return $this;
    }

    /**
     * Add attributes to profile or update existing
     * @param Attribute[]|array[] $newAttributes
     * @return Profile
     */
    public function setAttributes ($newAttributes) {
        if (!is_array($newAttributes)) {
            throw new \ErrorException('Argument "attributes" should be an array');
        }

        $attributes = $this->getAttributes();
        
        foreach ($newAttributes as $attr) {
            if (!($attr instanceof Attribute)) {
                $attr = $this->createAttribute(
                    isset($attr['collectApp']) ? $attr['collectApp'] : null,
                    isset($attr['section']) ? $attr['section'] : null,
                    isset($attr['name']) ? $attr['name'] : null,
                    isset($attr['value']) ? $attr['value'] : null
                );
            }

            if (!$attr->isValid()) {
                throw new \ErrorException('Attribute is not valid');
            }
            
            $foundAttr = $this->getAttribute(
                $attr->getName(),
                $attr->getCollectApp(),
                $attr->getSection()
            );
            
            if ($foundAttr) {
                $foundAttr->setValue($attr->getValue());
            } else {
                $attributes[] = $attr;
            }
        }
        
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get sessions. Can be filtered by passed function.
     * @param function $filter
     * @return Session[]
     */
    public function getSessions ($filter = null) {
        $sessions = $this->sessions;

        if ($filter) {
            if (!is_callable($filter)) {
                throw new Error('filter should be a function');
            }
            $sessions = array_filter($sessions, $filter);
        }

        return $sessions;
    }

    /**
     * Add session to profile or replace existing
     * @param Session|array $session
     * @return Session
     */
    public function setSession ($session) {
        if (!($session instanceof Session)) {
            $session = $this->createSession($session);
        }

        if (!$session->isValid()) {
            throw new \ErrorException('Session is not valid');
        }
        
        $existSession = $this->getSession($session->getId());
        
        if (!$existSession) {
            // add new session
            $this->sessions[] = $session;
        } elseif ($existSession !== $session) {
            // replace existing with new one
            $this->replaceSession($existSession, $session);
        }

        return $session;
    }

    /**
     * Get session by id
     * @param  string $sessionId
     * @return Session
     */
    public function getSession ($sessionId) {
        $sessions = array_filter($this->getSessions(), function ($session) use ($sessionId) {
            return $session->getId() === $sessionId;
        });
        $keys = array_keys($sessions);
        return count($keys) ? $sessions[$keys[0]] : null;
    }

    /**
     * Get last session
     * @return Session
     */
    public function getLastSession () {
        $sessions = $this->getSessions();
        $lastSession = null;

        if (count($sessions)) {
            usort($sessions, function ($a, $b) {
                return $b->getModifiedAt() - $a->getModifiedAt();
            });
            $lastSession = isset($sessions[0]) ? $sessions[0] : null;
        }

        return $lastSession;
    }

    /**
     * Serialize profile to JSON
     * @return array
     */
    public function serialize () {
        return array(
            'id' =>         $this->getId(),
            'attributes' => $this->serializeAttributes(),
            'sessions' =>   $this->serializeSessions()
        );
    }

    /**
     * Serialize attributes from Attribute instance to array
     * @return array
     */
    protected function serializeAttributes () {
        $attributesMap = array();

        foreach ($this->getAttributes() as $attribute) {
            $collectApp = $attribute->getCollectApp();
            $section = $attribute->getSection();
            $key = $collectApp . '/' . $section;

            if (!isset($attributesMap[$key])) {
                $attributesMap[$key] = array(
                    'collectApp' => $collectApp,
                    'section' => $section,
                    'data' => array()
                );
            }

            $attributesMap[$key]['data'][$attribute->getName()] = $attribute->getValue();
        }

        return array_values($attributesMap);
    }

    /**
     * Serialize sessions from Session instance to array
     * @return array
     */
    protected function serializeSessions () {
        return array_map(function ($session) {
            return $session->serialize();
        }, $this->getSessions());
    }

    /**
     * Sort sessions by modifiedAt property
     * @return Profile
     */
    protected function sortSessions () {
        $sessions = $this->getSessions();
        usort($sessions, function ($session1, $session2) {
            return $session1->getModifiedAt() - $session2->getModifiedAt();
        });
        $this->sessions = $sessions;
        return $this;
    }

    /**
     * Merge data from passed profile to current
     * @param $profile
     * @return Profile
     */
    public function merge ($profile) {
        if (!($profile instanceof Profile)) {
            throw new \ErrorException('Argument "profile" should be a Profile instance');
        }

        if ($this->getId() !== $profile->getId()) {
            throw new \ErrorException('Profile IDs should be similar');
        }

        // merge attributes
        $this->setAttributes($profile->getAttributes());

        $sessionsMap = array();
        foreach ($this->getSessions() as $session) {
            $sessionsMap[$session->getId()] = $session;
        }
        
        foreach ($profile->getSessions() as $session) {
            $sessionsMap[$session->getId()] = $session;
            $id = $session->getId();
            if (!isset($sessionsMap[$id])) {
                $sessionsMap[$id] = $session;
            } else {
                $sessionsMap[$id]->merge($session);
            }
        }

        $this->sessions = array_values($sessionsMap);

        $this->sortSessions();
        return $this;
    }
    
    
    /**
     * Create attributes by initial data
     * @param array $rawAttributesData
     * @return Profile
     */
    protected function initAttributes ($rawAttributesData) {
        if (is_array($rawAttributesData)) {
            $attributes = array();
            foreach ($rawAttributesData as $attr) {
                if (count($attr['data'])) {
                    $attributes = array_merge($attributes, $this->createAttributes(
                        isset($attr['collectApp']) ? $attr['collectApp'] : null,
                        isset($attr['section']) ? $attr['section'] : null,
                        isset($attr['data']) ? $attr['data'] : null
                    ));
                }
            }
            
            $this->attributes = $attributes;
        }

        return $this;
        
    }
   
    /**
     * Create session by initial data
     * @param array $rawSessionsData
     * @return Profile
     */
    protected function initSessions ($rawSessionsData) {
        if (is_array($rawSessionsData)) {
            foreach ($rawSessionsData as $rawSessionData) {
                $this->sessions[] = $this->createSession($rawSessionData);
            }
        }

        return $this;
    }


    /**
     * Replace existing session with other one
     * @param Session $oldSession
     * @param Session $newSession
     * @return Profile
     */
    protected function replaceSession ($oldSession, $newSession) {
        $sessions = $this->getSessions();
        $index = array_search($oldSession, $sessions);

        if ($index !== false) {
            $this->sessions[$index] = $newSession;
        }

        return $this;
    }

    /**
     * Create session
     * @param array $rawSessionData
     * @return Session
     */
    protected function createSession ($rawSessionData) {
        return new Session($rawSessionData);
    }    
}
