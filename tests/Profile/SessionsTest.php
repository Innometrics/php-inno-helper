<?php

namespace Profile;

use Innometrics\Session;

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

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Session is not valid
     */
    public function testShouldThrowErrorIfSessionIsInvalid () {
        $profile = $this->createProfile();
        $profile->setSession(['id' => "asd"]);
    }

    public function testShouldSetSession () {
        $profile = $this->createProfile();
        $session1 = [
            'id'        => 'qwe',
            'collectApp'=> 'app',
            'section'   => 'sec'
        ];
        $session2 = new Session([
            'id'        => 'asd',
            'collectApp'=> 'app2',
            'section'   => 'sec2'
        ]);

        $this->assertEquals(count($profile->getSessions()) , 0);

        $profile->setSession($session1);
        $this->assertEquals(count($profile->getSessions()), 1);

        $profile->setSession($session2);
        $this->assertEquals(count($profile->getSessions()), 2);
    }

    public function testShouldReplaceSessionIfExistsWithSameId () {
        $profile = $this->createProfile();
        $session1 = [
            'id'        => 'qwe',
            'collectApp'=> 'app',
            'section'   => 'sec'
        ];
        $session2 = new Session([
            'id'        => 'qwe',
            'collectApp'=> 'app2',
            'section'   => 'sec2'
        ]);

        $this->assertEquals(count($profile->getSessions()) , 0);

        $profile->setSession($session1);
        $this->assertEquals(count($profile->getSessions()), 1);

        $profile->setSession($session2);
        $this->assertEquals(count($profile->getSessions()), 1);

        $session = $profile->getSessions()[0];
        $this->assertEquals($session->getCollectApp(), 'app2');
        $this->assertEquals($session->getSection(), 'sec2');
    }

    public function testShouldIgnoreSessionIfThisOneAlreadyInProfile () {
        $profile = $this->createProfile();
        $session1 = new Session([
            'id'        => 'qwe',
            'collectApp'=> 'app',
            'section'   => 'sec'
        ]);
        $this->assertEquals(count($profile->getSessions()) , 0);

        $profile->setSession($session1);
        $this->assertEquals(count($profile->getSessions()), 1);

        $profile->setSession($session1);
        $this->assertEquals(count($profile->getSessions()), 1);
        $session = $profile->getSessions()[0];

        $this->assertEquals($session, $session1);
    }

    public function testShouldReturnSession () {
        $profile = $this->createProfile();
        $this->assertNull($profile->getSession('no existing'));
        $profile->setSession([
            'id'        => 'sid',
            'collectApp'=> 'app',
            'section'   => 'sec'
        ]);
        $this->assertNull($profile->getSession('no existing'));
        $this->assertEquals($profile->getSession('sid')->getId(), 'sid');
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage filter should be a function
     */
    public function testShouldThrowErrorIfFilterNoAFunction () {
        $profile = $this->createProfile();
        $profile->getSessions('non func');
        $profile->getSessions(true);
        $profile->getSessions([]);
    }

    



}





































