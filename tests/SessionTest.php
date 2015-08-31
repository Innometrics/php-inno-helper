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

}
