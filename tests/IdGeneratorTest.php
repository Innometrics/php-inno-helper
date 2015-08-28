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

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Length should be positive
     */
    public function testShouldBeReturnErrorIfMethodGenerateCallWithNegativeLength () {
        $generator = $this->createGenerator();
        $generator->generate(-1);
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Length should be a number
     */
    public function testShouldBeReturnErrorIfMethodGenerateCallWithDontNumberLength () {
        $generator = $this->createGenerator();
        $generator->generate('-1');
    }

}
