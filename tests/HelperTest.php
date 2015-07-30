<?php

require_once __DIR__ . '/../lib/Innometrics/Helper.php';

use Innometrics\Helper;

class HelperTest extends PHPUnit_Framework_TestCase {
    
    protected function createHelper ($config = array()) {
        return new Helper($config);
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Config should be a non-empty array
     */     
    public function testShouldThrowErrorOnEmptyConfig () {
        $this->createHelper();
    }

}
