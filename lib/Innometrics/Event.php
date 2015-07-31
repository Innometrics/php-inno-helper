<?php

namespace Innometrics;

use Innometrics\IdGenerator;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Event {

    /**
     * Event id
     * @var string
     */
    protected $id = null;

    /**
     * Event definition id
     * @var string
     */
    protected $definitionId = null;

    /**
     * Event data object
     * @var array
     */
    protected $data = array();

    /**
     * Date when event was created (timestamp in ms)
     * @var double
     */
    protected $createdAt = null;

    /**
     * @param array $config equals to {id: id, definitionId: definitionId, data: data, createdAt: timestamp}
     */
    public function __construct($config = array()) {
        $now = round(microtime(true) * 1000);
            
        $this->setId(isset($config['id']) ? $config['id'] : IdGenerator::generate(8));
        $this->setData(isset($config['data']) ? $config['data'] : array());
        $this->setDefinitionId(isset($config['definitionId']) ? $config['definitionId'] : null);
        $this->setCreatedAt(isset($config['createdAt']) ? $config['createdAt'] : $now);
    }

    /**
     * Set event id
     * @param string $id
     * @return Event
     */
    public function setId ($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Set date (in ms) when event was created
     * Double or DateTime instance can be used.
     * @param double|DateTime date
     * @return Event
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
     * Set event definition id
     * @param string $definitionId
     * @return Event
     */
    public function setDefinitionId ($definitionId) {
        $this->definitionId = $definitionId;
        return $this;
    }

    /**
     * Update event data with values
     * Data is an array with key=>value pair(s)
     *
     * @param array $data
     * @return Event
     */
    public function setData ($data) {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Set single value of event data
     * @param string $name
     * @param mixed $value
     * @return Event
     */
    public function setDataValue ($name, $value) {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Get event id
     * @return string
     */
    public function getId () {
        return $this->id;
    }

    /**
     * Get date (in ms) when event was created
     * @return integer
     */
    public function getCreatedAt () {
        return $this->createdAt;
    }

    /**
     * Get event definition id
     * @return string
     */
    public function getDefinitionId () {
        return $this->definitionId;
    }

    /**
     * Get event data array
     * @return array
     */
    public function getData () {
        return $this->data;
    }

    /**
     * Get single value of event data array
     * @param string $name
     * @return mixed
     */
    public function getDataValue ($name) {
        return $this->data && isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Check if event is valid (all required fields are present)
     * @return bool
     */
    public function isValid () {
        return !!($this->getId() && $this->getDefinitionId() && $this->getData() && $this->getCreatedAt());
    }

    /**
     * Convert event to JSON
     * @return array
     */
    public function serialize () {
        return array(
            'id' =>           $this->getId(),
            'data' =>         (object)$this->getData(),
            'definitionId' => $this->getDefinitionId(),
            'createdAt' =>    $this->getCreatedAt()
        );
    }

    /**
     * Merge event with same id to current
     * @param Event $event
     * @return Event
     */
    public function merge ($event) {
        if (!($event instanceof Event)) {
            throw new \ErrorException('Argument "event" should be a Event instance');
        }

        if ($this->getId() !== $event->getId()) {
            throw new \ErrorException('Event IDs should be similar');
        }

        $this->setData($event->getData());

        return $this;
    }
}