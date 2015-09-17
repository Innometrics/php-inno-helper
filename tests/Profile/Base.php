<?php

namespace Profile;

require_once('vendor/autoload.php');

use Innometrics\Profile;

class Base extends \PHPUnit_Framework_TestCase {
    
    protected $profile = null;
    
    protected $config = array();
    
    protected function setUp() {}
    
    protected function tearDown() {}

    protected function createProfile ($config = null) {
        $profile = new Profile($config ?: $this->config);
        $this->profile = $profile;
        return $profile;
    }
}
