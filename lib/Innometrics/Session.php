<?php

namespace Innometrics;

use Innometrics\Event;
use Innometrics\IdGenerator;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Session {

    /**
     * Session id
     * @var string
     */
    protected $id = null;

    /**
     * Session section name
     * @var string
     */
    protected $section = null;

    /**
     * Session application name
     * @var string
     */
    protected $collectApp = null;

    /**
     * Timestamp in ms when session was created
     * @var integer
     */
    protected $createdAt = null;

    /**
     * Timestamp in ms when session was modified
     * @var integer
     */
    protected $modifiedAt = null;

    /**
     * Session data object
     * @var array
     */
    protected $data = array();

    /**
     * Session events
     * @var array
     */
    protected $events = array();
    
    /**
     * @param array $config equals to {id: id, section: sectionId, collectApp: collectApp, data: data, events: events, createdAt: timestamp, modifiedAt: modifiedAt }
     */
    public function __construct($config = array()) {
        $now = round(microtime(true) * 1000);

        $this->setId(isset($config['id']) ? $config['id'] : IdGenerator::generate(8));
        $this->setData(isset($config['data']) ? $config['data'] : array());
        $this->setCollectApp(isset($config['collectApp']) ? $config['collectApp'] : null);
        $this->setSection(isset($config['section']) ? $config['section'] : null);
        $this->setCreatedAt(isset($config['createdAt']) ? $config['createdAt'] : $now);
        $this->modifiedAt = isset($config['modifiedAt']) ? $config['modifiedAt'] : $now;
        $this->initEvents(isset($config['events']) ? $config['events'] : array());        
    }
    
    /**
     * Set session id
     * @param string $id
     * @return Session
     */
    public function setId ($id) {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Set session application name
     * @param string $collectApp
     * @return Session
     */
    public function setCollectApp ($collectApp) {
        $this->collectApp = $collectApp;
        return $this;
    }
    
    /**
     * Set session section name
     * @param string section
     * @return Session
     */
    public function setSection ($section) {
        $this->section = $section;
        return $this;
    }
    
    /**
     * Set timestamp when session was created
     * Passed argument should be a number or Date instance
     * @param double|DateTime date
     * @return Session
     */
    public function setCreatedAt ($date) {
        if (!is_double($date) && !($date instanceof \DateTime)) {
            throw new \ErrorException('Wrond date "' . $date . '". It should be an double or a DateTime instance.');
        }
        
        if ($date instanceof \DateTime) {
            $ts = $date->getTimestamp();
            $date = $ts * 1000;
        }
        
        $this->createdAt = $date;
        return $this;
    }
    
    /**
     * Update session data with values
     * Data is an object with key=>value pair(s).
     * @param array $data
     * @return Session
     */
    public function setData ($data) {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    /**
     * Set single value of session data
     * @param string $name
     * @param mixed $value
     * @return Session
     */
    public function setDataValue ($name, $value) {
        $this->data[$name] = $value;
        return $this;
    }
    
    /**
     * Get session id
     * @return string
     */
    public function getId () {
        return $this->id;
    }
    
    /**
     * Get session application name
     * @return string
     */
    public function getCollectApp () {
        return $this->collectApp;
    }
    
    /**
     * Get session section name
     * @return string
     */
    public function getSection () {
        return $this->section;
    }
    
    /**
     * Get timestamp in ms when session was created
     * @return integer
     */
    public function getCreatedAt () {
        return $this->createdAt;
    }
    
    /**
     * Get timestamp in ms when session was modified
     * @return integer
     */
    public function getModifiedAt () {
        return $this->modifiedAt;
    }
    
    /**
     * Get session data object
     * @return array
     */
    public function getData () {
        return $this->data;
    }
    
    /**
     * Get single value from session data object
     * @return string
     */
    public function getDataValue ($name) {
        return $this->data && isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Add event to session
     * If event with same id already exist in session then Error will be thrown
     * @param Event|array $event
     * @return Event
     */
    public function addEvent ($event) {
        if (!($event instanceof Event)) {
            $event = $this->createEvent($event);
        }

        if (!$event->isValid()) {
            throw new \ErrorException('Event is not valid');
        }

        $existEvent = $this->getEvent($event->getId());

        if ($existEvent) {
            throw new \ErrorException('Event with id "' . $event->getId() . '" already exists');
        }

        $events = $this->getEvents();
        $events[] = $event;

        return $event;
    }

    /**
     * Get event by $id
     * @param string $eventId
     * @return Event|null
     */
    public function getEvent ($eventId) {
        $events = array_filter($this->getEvents(), function ($event) use ($eventId) {
            return $event->getId() === $eventId;
        });
        $keys = array_keys($events);
        return count($keys) ? $events[$keys[0]] : null;        
    }

    /**
     * Get last event in session
     * @return Event|null
     */
    public function getLastEvent () {
        $events = $this->getEvents();
        $cnt = count($events);
        return $cnt ? $events[$cnt - 1] : null;
    }

    /**
     * Get events. Can be filtered by definition id
     * @param  string $eventDefinitionId
     * @return array
     */
    public function getEvents ($eventDefinitionId = null) {
        $events = $this->events;

        if ($eventDefinitionId) {
            $events = array_filter($events, function ($event) use ($eventDefinitionId) {
                return $event->getDefinitionId() === $eventDefinitionId;
            });
        }

        return $events;
    }
    
    /**
     *
     * @return bool
     */
    public function isValid () {
        return !!$this->getId() && !!$this->getSection() && !!$this->getCollectApp() && !!$this->getCreatedAt();
    }
    
    /**
     * Serialize session to JSON
     * @return array
     */
    protected function serialize () {
        return array(
            'id' =>         $this->getId(),
            'section' =>    $this->getSection(),
            'collectApp' => $this->getCollectApp(),
            'data' =>       $this->getData(),
            'events' =>     $this->serializeEvents(),
            'createdAt' =>  $this->getCreatedAt(),
            'modifiedAt' => $this->getModifiedAt()
        );
    }

    /**
     * Sort events
     * @return Session
     */
    protected function sortEvents () {
        $events = $this->getEvents();
        usort($events, function ($event1, $event2) {
            return $event1->getCreatedAt() - $event2->getCreatedAt();
        });
        
        $this->events = $events;
        return $this;
    }

    /**
     * Merge data from passed session to current
     * @param Session session
     * @return Session
     */
    protected function merge ($session) {
        if (!($session instanceof Session)) {
            throw new \ErrorException('Argument "session" should be a Session instance');
        }

        if ($this->getId() !== session.getId()) {
            throw new \ErrorException('Session IDs should be similar');
        }

        // update last modified date
        if ($session->modifiedAt > $this->modifiedAt) {
            $this->modifiedAt = $session->modifiedAt;
        }

        // merge data
        $this->setData($session->getData());

        // merge events
        $eventsMap = array();
        foreach ($this->getEvents() as $event) {
            $eventsMap[$event->getId()] = $event;
        }
        
        foreach ($session->getEvents() as $event) {
            $id = $event->getId();
            if (!isset($eventsMap[$id])) {
                $eventsMap[$id] = $event;
            } else {
                $eventsMap[$id]->merge($event);
            }
        }

        $this->events = array_values($eventsMap);

        $this->sortEvents();
        return $this;
    }

    /**
     * Create session events by initial data
     * @param array $rawEventsData
     * @return Session
     */
    private function initEvents ($rawEventsData) {
        $this->events = array();

        if (is_array($rawEventsData)) {
            foreach ($rawEventsData as $rawEventData) {
                $this->events[] = $this->createEvent($rawEventData);
            }
        }

        return $this;
    }

    /**
     * Create event
     * @param array rawEventData
     * @return Event
     */
    private function createEvent ($rawEventData) {
        return new Event($rawEventData);
    }

    /**
     * Serialize events to JSON
     * @return array
     */
    private function serializeEvents () {
        return array_map($this->getEvents(), function ($event) {
            return $event->serialize();
        });
    }
    
}
