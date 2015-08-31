<?php

require_once __DIR__ . '/../lib/Innometrics/IdGenerator.php';

use Innometrics\IdGenerator;

class IdGeneratorTest extends PHPUnit_Framework_TestCase {

    protected function createGenerator () {
        return IdGenerator::getInstance();
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Length should be positive
     */
    public function testShouldBeReturnErrorIfMethodGenerateCallWithNegativeLength () {
        IdGenerator::generate(-1);
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Length should be a number
     */
    public function testShouldBeReturnErrorIfMethodGenerateCallWithDontNumberLength () {
        IdGenerator::generate('-1');
    }

}
