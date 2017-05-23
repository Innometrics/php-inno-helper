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

        $this->assertCount(0, $profile->getSessions());

        $profile->setSession($session1);
        $this->assertCount(1, $profile->getSessions());

        $profile->setSession($session2);
        $this->assertCount(2, $profile->getSessions());
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

        $this->assertCount(0, $profile->getSessions());

        $profile->setSession($session1);
        $this->assertCount(1, $profile->getSessions());

        $profile->setSession($session2);
        $this->assertCount(1, $profile->getSessions());

        $session = $profile->getSessions()[0];
        $this->assertEquals('app2', $session->getCollectApp());
        $this->assertEquals('sec2', $session->getSection());
    }

    public function testShouldIgnoreSessionIfThisOneAlreadyInProfile () {
        $profile = $this->createProfile();
        $session1 = new Session([
            'id'        => 'qwe',
            'collectApp'=> 'app',
            'section'   => 'sec'
        ]);
        $this->assertCount(0, $profile->getSessions());

        $profile->setSession($session1);
        $this->assertCount(1, $profile->getSessions());

        $profile->setSession($session1);
        $this->assertCount(1, $profile->getSessions());
        $session = $profile->getSessions()[0];

        $this->assertEquals($session1, $session);
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
        $this->assertEquals('sid', $profile->getSession('sid')->getId());
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

    public function testShouldReturnAllSessionsIfNoFilterFunction () {
        $profile = $this->createProfile();
        $sessions = $profile->getSessions();
        $this->assertCount(0, $sessions);

        $profile->setSession([
            'id'        => 'sid1',
            'collectApp'=> 'app1',
            'section'   => 'sec1'
        ]);
        $profile->setSession([
            'id'        => 'sid2',
            'collectApp'=> 'app2',
            'section'   => 'sec2'
        ]);

        $sessions = $profile->getSessions();
        $this->assertCount(2, $sessions);
    }

    public function testShouldReturnOnlyFilteredSessions () {
        $profile = $this->createProfile();
        $profile->setSession([
            'id'        => 'sid1',
            'collectApp'=> 'app1',
            'section'   => 'sec1'
        ]);
        $profile->setSession([
            'id'        => 'sid2',
            'collectApp'=> 'app2',
            'section'   => 'sec2'
        ]);

        $sessions = $profile->getSessions(function ($session) {
            return $session->getCollectApp() === 'app2';
        });
        $this->assertCount(1, $sessions);

        $session = $sessions[0];
        $this->assertEquals('sid2', $session->getId());
        $this->assertEquals('app2', $session->getCollectApp());
        $this->assertEquals('sec2', $session->getSection());
    }

    public function testShouldReturnNullIfNoLastSession () {
        $profile = $this->createProfile();
        $this->assertNull($profile->getLastSession());
    }

    public function testShouldReturnLastSession () {
        $profile = $this->createProfile();
        $profile->setSession([
            'id'        => 'sid1',
            'collectApp'=> 'app1',
            'section'   => 'sec1',
            'createdAt' => 1000000000000
        ]);
        $profile->setSession([
            'id'        => 'sid2',
            'collectApp'=> 'app2',
            'section'   => 'sec2',
            'createdAt' => 1000000000001
        ]);

        $session = $profile->getLastSession();
        $this->assertEquals('sid2', $session->getId());

        $profile->getSession('sid1')->addEvent([
            'id' => 'e1',
            'definitionId' => 'b1',
            'createdAt' => 1000000000004
        ]);
        $profile->getSession('sid2')->addEvent([
            'id' => 'e2',
            'definitionId' => 'b2',
            'createdAt' => 1000000000003
        ]);

        $session = $profile->getLastSession();
        $this->assertEquals('sid1', $session->getId());
    }

}





































