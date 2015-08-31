<?php

require_once __DIR__ . '/../lib/Innometrics/Profile.php';

use Innometrics\Profile;

class ProfileTest extends PHPUnit_Framework_TestCase {

    protected function createProfile ($config = array()) {
        return new Profile($config);
    }

    public function testShouldBeRetirnId () {
        $profile = $this->createProfile(array(
            'id' => 'pid'
        ));
        $this->assertEquals('pid', $profile->getId());
    }

}
