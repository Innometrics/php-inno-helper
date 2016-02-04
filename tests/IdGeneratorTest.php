<?php

require_once __DIR__ . '/../lib/Innometrics/IdGenerator.php';

use Innometrics\IdGenerator;

class IdGeneratorTest extends PHPUnit_Framework_TestCase {

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

    public function testShouldGenerateIdWithDefaultLength () {
        $defaultLength = 32;
        $id = IdGenerator::generate();
        $this->assertEquals($defaultLength, strlen($id));
    }

    public function testShouldGenerateIdWithDefinedLength () {
        $length = 100;
        $id = IdGenerator::generate($length);
        $this->assertEquals($length, strlen($id));
    }

}
