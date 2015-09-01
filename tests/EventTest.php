<?php

require_once __DIR__ . '/../lib/Innometrics/Event.php';

use Innometrics\Event;

class EventTest extends PHPUnit_Framework_TestCase {

    protected function createEvent ($config = array()) {
        return new Event($config);
    }

    public function testShouldNotThrowErrorOnIsValid () {
        $event = $this->createEvent(array(
            'definitionId' => 'name1',
            'data'         => array('name1' => 'value1')
        ));
        $this->assertTrue($event->isValid());
    }

    public function testShouldReceiveData () {
        $event = $this->createEvent(array(
            'definitionId' => 'name1',
            'data'         => array('name1' => 'value1')
        ));

        $event->setDataValue('name1', 'newvalue1');
        $this->assertEquals('newvalue1', $event->getDataValue('name1'), 'getDataValue test');
    }

    public function testShouldReceiveSerializeData () {
        $now = round(microtime(true) * 1000);

        $event = $this->createEvent(array(
            'id'           => 'eid',
            'definitionId' => 'name1',
            'data'         => array('name1' => 'value1'),
            'createdAt'    => $now
        ));

        $this->assertEquals((object) array(
            'id'           => 'eid',
            'definitionId' => 'name1',
            'data'         => (object) array('name1' => 'value1'),
            'createdAt'    => $now
        ), $event->serialize(), 'serialize test');
    }

    public function testShouldReceiveMergedData () {
        $now = round(microtime(true) * 1000);

        $event1 = $this->createEvent(array(
            'id'           => 'eid1',
            'definitionId' => 'name1',
            'data'         => array('name1' => 'value1'),
            'createdAt'    => $now
        ));

        $event2 = $this->createEvent(array(
            'id'           => 'eid1',
            'definitionId' => 'name1',
            'data'         => array('name2' => 'value2'),
            'createdAt'    => $now
        ));

        $event1->merge($event2);

        $this->assertEquals((object) array(
            'id'           => 'eid1',
            'definitionId' => 'name1',
            'data'         => (object) array('name1' => 'value1', 'name2' => 'value2'),
            'createdAt'    => $now
        ), $event1->serialize(), 'merge test');

        $event2->setId('eid2');
        try {
            $event1->merge($event2);
        } catch (\Exception $e) {
            $this->assertEquals('Event IDs should be similar', $e->getMessage());
        }
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Wrond date "date". It should be an double or a DateTime instance.
     */
    public function testShouldThrowErrorOnNoValidDate () {
        $event = $this->createEvent(array(
            'definitionId' => 'name1',
            'data'         => array('name1' => 'value1')
        ));

        $event->setCreatedAt('date');
    }

    public function testShouldSupportDateTimeObjectForsetCreateAt () {
        $event = $this->createEvent(array(
            'definitionId' => 'name1',
            'data'         => array('name1' => 'value1')
        ));
        $now = new DateTime('now');
        $event->setCreatedAt($now);
        $this->assertEquals($now->getTimestamp() * 1000, $event->getCreatedAt());
    }

}
