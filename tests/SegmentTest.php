<?php

require_once __DIR__ . '/../lib/Innometrics/Segment.php';

use Innometrics\Segment;

class SegmentTest extends PHPUnit_Framework_TestCase {
    
    protected function createSegment ($config = array()) {
        return new Segment($config);
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Config should be a non-empty array
     */    
    public function testShouldThrowErrorOnEmptyConfig () {
        $this->createSegment();
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Config should be a non-empty array
     */    
    public function testShouldThrowErrorOnNonArrayConfig () {
        $this->createSegment(123);
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Property "id" in config should be defined
     */    
    public function testShouldThrowErrorOnUndefinedId () {
        $this->createSegment(array(
            'iql' => 123
        ));
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Property "id" in config should be a string
     */    
    public function testShouldThrowErrorOnNonStringId () {
        $this->createSegment(array(
            'id' => 123
        ));
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Property "id" in config can not be empty
     */    
    public function testShouldThrowErrorOnEmptyId () {
        $this->createSegment(array(
            'id' => '  '
        ));
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Property "iql" in config should be defined
     */    
    public function testShouldThrowErrorOnUndefinedIQL () {
        $this->createSegment(array(
            'id' => '123'
        ));
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Property "iql" in config should be a string
     */    
    public function testShouldThrowErrorOnNonStringIQL () {
        $this->createSegment(array(
            'id' => '123',
            'iql' => 123
        ));
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Property "iql" in config can not be empty
     */    
    public function testShouldThrowErrorOnEmptyIQL () {
        $this->createSegment(array(
            'id' => '123',
            'iql' => '  '
        ));
    }

    public function testShouldNotThrowErrorOnIsValid () {
        $segment = $this->createSegment(array(
            'id' => '123',
            'iql' => 'collectApp("web").section("9")'
        ));
        
        $this->assertTrue($segment->isValid());
    }

    public function testShouldReceiveProperties () {
        $id = '123';
        $iql = 'collectApp("web").section("9")';
        
        $segment = $this->createSegment(array(
            'id' => $id,
            'iql' => $iql
        ));
        
        $this->assertEquals($id, $segment->getId(), 'getId test');
        $this->assertEquals($iql, $segment->getIql(), 'getIql test');
    }

}
