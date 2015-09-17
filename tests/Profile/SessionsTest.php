<?php

namespace Profile;

require_once('vendor/autoload.php');

class SessionsTest extends Base {
    
    public function testShouldCreateSessionsFromConfig () {
        $profile = $this->createProfile(array(
            'id' => 'pid',
            'sessions' => array(
                array(
                    'collectApp' => 'app',
                    'section' => 'sec',
                    'data' => array(),
                    'events' => array()
                )
            )
        ));
        
        $sessions = $profile->getSessions();
        
        $this->assertCount(1, $sessions);
        
        $session = $sessions[0];
        $this->assertEquals($session->getCollectApp(), 'app');
        $this->assertEquals($session->getSection(), 'sec');
    }
    
}
