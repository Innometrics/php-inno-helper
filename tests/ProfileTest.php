<?php

require_once __DIR__ . '/../lib/Innometrics/Profile.php';

use Innometrics\Profile;

class ProfileTest extends PHPUnit_Framework_TestCase {

    protected function createProfile ($config = array()) {
        return new Profile($config);
    }

    public function testShouldBeRetirnSomeData () {
        $profile = $this->createProfile(array(
            'id' => 'pid',
            'attributes' => array(
                array(
                    'collectApp' => 'collectApp1',
                    'section' => 'section1',
                    'data' => array('name1' => 'value1')
                ),
                array(
                    'collectApp' => 'collectApp2',
                    'section' => 'section2',
                    'data' => array('name2' => 'value2')
                )
            ),
            'sessions' => array(
                array(
                    'id' => 'sid',
                    'collectApp' => 'collectApp1',
                    'section' => 'section1',
                    'data' => array('name1' => 'value1'),
                    'events' => array(
                        array(
                            'definitionId' => 'name1',
                            'data' => array('name1' => 'value1')
                        )
                    )
                ),
                array(
                    'collectApp' => 'collectApp2',
                    'section' => 'section2',
                    'data' => array('name2' => 'value2'),
                    'events' => array(
                        array(
                            'definitionId' => 'name2',
                            'data' => array('name2' => 'value2')
                        )
                    )
                )
            )
        ));
        $this->assertEquals('pid', $profile->getId());
        $attributes = $profile->getAttributes();
        $this->assertEquals(count($attributes), 2);
        $attributes = $profile->getAttributes('collectApp1');
        $this->assertEquals(count($attributes), 1);
        $attributes = $profile->getAttributes(null, 'section1');
        $this->assertEquals(count($attributes), 1);
        $attribute = $profile->getAttribute('name1', 'collectApp1', 'section1');
        $this->assertEquals($attribute->getValue(), 'value1');
        $sessions = $profile->getSessions(function ($session) {
            return $session->getCollectApp() === 'collectApp1';
        });
        $this->assertEquals(count($sessions), 1);
        $session = $profile->getSession('sid');
        $this->assertEquals($session->getDataValue('name1'), 'value1');
    }

}
