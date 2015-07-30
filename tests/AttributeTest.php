<?php

require_once __DIR__ . '/../lib/Innometrics/Attribute.php';

use Innometrics\Attribute;

class AttributeTest extends PHPUnit_Framework_TestCase {
    
    protected function createAttribute ($config = array()) {
        return new Attribute($config);
    }
    
    public function testShouldNotThrowErrorOnEmptyConfig () {
        $this->createAttribute();
    }

}
