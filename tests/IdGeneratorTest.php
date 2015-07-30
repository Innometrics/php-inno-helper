<?php

require_once __DIR__ . '/../lib/Innometrics/IdGenerator.php';

use Innometrics\IdGenerator;

class IdGeneratorTest extends PHPUnit_Framework_TestCase {
    
    protected function createGenerator () {
        return IdGenerator::getInstance();
    }    
    
    public function testShouldHasMethodGenerate () {
        $this->assertTrue(method_exists($this->createGenerator(), 'generate'));
    }

}
