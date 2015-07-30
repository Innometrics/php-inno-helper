<?php

require_once __DIR__ . '/../lib/Innometrics/Event.php';

use Innometrics\Event;

class EventTest extends PHPUnit_Framework_TestCase {
    
    protected function createEvent ($config = array()) {
        return new Event($config);
    }
    
    public function testShouldNotThrowErrorOnEmptyConfig () {
        $this->createEvent();
    }

}
