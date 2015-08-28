<?php

require_once __DIR__ . '/../lib/Innometrics/Session.php';

use Innometrics\Session;

class SessionTest extends PHPUnit_Framework_TestCase {

    protected function createSession ($config = array()) {
        return new Session($config);
    }

    public function testShouldThrowErrorOnEmptyConfig () {
        $this->createSession();
    }

}
