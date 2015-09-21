<?php

require_once __DIR__ . '/../lib/Innometrics/Session.php';

use Innometrics\Session;

class SessionTest extends PHPUnit_Framework_TestCase {

    protected function createSession ($config = array()) {
        return new Session($config);
    }

    public function testShouldNotThrowErrorOnIsValid () {
        $session = $this->createSession(array(
            'collectApp' => 'collectApp1',
            'section' => 'section1',
            'data' => array('name1' => 'value1'),
            'events' => array(
                array(
                    'definitionId' => 'name1',
                    'data' => array('name1' => 'value1')
                ),
                array(
                    'definitionId' => 'name2',
                    'data' => array('name2' => 'value2')
                )
            )
        ));
        $this->assertTrue($session->isValid());
    }

    public function testShouldReceiveMergedData () {
        $session1 = $this->createSession(array(
            'id' => 'sid1',
            'collectApp' => 'collectApp1',
            'section' => 'section1',
            'data' => array('name1' => 'value1'),
            'events' => array(
                array(
                    'id' => 'eid1',
                    'definitionId' => 'name1',
                    'data' => array('name1' => 'value1')
                ),
                array(
                    'definitionId' => 'name2',
                    'data' => array('name2' => 'value2')
                )
            )
        ));

        $session2 = $this->createSession(array(
            'id' => 'sid1',
            'collectApp' => 'collectApp1',
            'section' => 'section1',
            'data' => array('name1' => 'value1'),
            'events' => array(
                array(
                    'id' => 'eid1',
                    'definitionId' => 'name1',
                    'data' => array('name1' => 'value1')
                ),
                array(
                    'definitionId' => 'name3',
                    'data' => array('name3' => 'value3')
                )
            )
        ));

        $session1->merge($session2);
        $this->assertEquals(count($session1->getEvents()), 3, 'merge test');

        $session2->setId('sid2');
        try {
            $session1->merge($session2);
        } catch (\Exception $e) {
            $this->assertEquals('Session IDs should be similar', $e->getMessage());
        }
    }

    public function testShouldReceiveSerializeData () {
        $now = round(microtime(true) * 1000);

        $session = $this->createSession(array(
            'id' => 'sid1',
            'collectApp' => 'collectApp1',
            'section' => 'section1',
            'data' => array('name1' => 'value1'),
            'events' => array(
                array(
                    'id' => 'eid1',
                    'definitionId' => 'name1',
                    'data' => array('name1' => 'value1'),
                    'createdAt' => $now
                )
            ),
            'createdAt' => $now,
            'modifiedAt' => $now
        ));

        $this->assertEquals((object) array(
            'id' => 'sid1',
            'collectApp' => 'collectApp1',
            'section' => 'section1',
            'data' => (object)array('name1' => 'value1'),
            'events' => array(
                (object) array(
                    'id' => 'eid1',
                    'definitionId' => 'name1',
                    'data' => (object) array('name1' => 'value1'),
                    'createdAt' => $now
                )
            ),
            'createdAt' => $now,
            'modifiedAt' => $now
        ), $session->serialize(), 'serialize test');
    }

    public function testShouldBeCanWorkWithEvents () {
        $now = round(microtime(true) * 1000);

        $session = $this->createSession(array(
            'id' => 'sid1',
            'collectApp' => 'collectApp1',
            'section' => 'section1',
            'data' => array('name1' => 'value1'),
            'events' => array(
                array(
                    'id' => 'eid1',
                    'definitionId' => 'name1',
                    'data' => array('name1' => 'value1'),
                    'createdAt' => $now
                )
            ),
            'createdAt' => $now,
            'modifiedAt' => $now
        ));

        $session->setDataValue('name2', 'value2');
        $this->assertEquals($session->getDataValue('name2'), 'value2');

        $event = $session->getEvent('eid1');
        $this->assertEquals((object) array(
            'id' => 'eid1',
            'definitionId' => 'name1',
            'data' => (object) array('name1' => 'value1'),
            'createdAt' => $now
        ), $event->serialize());

        $event = $session->getLastEvent();
        $this->assertEquals((object) array(
            'id' => 'eid1',
            'definitionId' => 'name1',
            'data' => (object)array('name1' => 'value1'),
            'createdAt' => $now
        ), $event->serialize());

        $session->addEvent(array(
            'definitionId' => 'name1',
            'data' => array('name1' => 'value1')
        ));
        $this->assertEquals(count($session->getEvents('name1')), 2);
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Event is not valid
     */
    public function testShouldThrowErrorOnNoValidEvent () {
        $session = $this->createSession(array(
            'collectApp' => 'collectApp1',
            'section' => 'section1'
        ));

        $session->addEvent(array(
            'id' => 'eId',
            'definitionId' => false
        ));
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Event with id "eid1" already exists
     */
    public function testShouldThrowErrorOnEventIsExist () {
        $session = $this->createSession(array(
            'collectApp' => 'collectApp1',
            'section' => 'section1',
            'events' => array(
                array(
                    'id' => 'eid1',
                    'definitionId' => 'name1',
                    'data' => array('name1' => 'value1')
                )
            ),
        ));

        $session->addEvent(array(
            'id' => 'eid1',
            'definitionId' => 'name1',
            'data'         => array('name1' => 'value1')
        ));
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Wrond date "date". It should be an double or a DateTime instance.
     */
    public function testShouldThrowErrorOnNoValidDate () {
        $session = $this->createSession(array(
            'collectApp' => 'collectApp1',
            'section' => 'section1'
        ));

        $session->setCreatedAt('date');
    }

    public function testShouldSupportDateTimeObjectForsetCreateAt () {
        $session = $this->createSession(array(
            'collectApp' => 'collectApp1',
            'section' => 'section1'
        ));

        $now = new DateTime('now');
        $session->setCreatedAt($now);
        $this->assertEquals($now->getTimestamp() * 1000, $session->getCreatedAt());
    }

    public function testShouldBeMarkedAsDirtyAfterCreation () {
        $session = $this->createSession();
        $this->assertTrue($session->hasChanges());
    }

    public function testShouldBeMarkerAsNotDirty () {
        $session = $this->createSession();
        $session->resetDirty();
        $this->assertFalse($session->hasChanges());
    }

    public function testShouldBeMarkerAsDirtyIfAnyEventIsDirty () {
        $session = $this->createSession();
        $session->addEvent(array(
            'definitionId'=> 'ed1'
        ));
        $session->resetDirty();
        $this->assertFalse($session->hasChanges());
        $session->getLastEvent()->setDataValue('a', 'b');
        $this->assertTrue($session->hasChanges());
    }

}
