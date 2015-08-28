<?php

require_once __DIR__ . '/../lib/Innometrics/Cache.php';

use Innometrics\Cache;

class CacheTest extends PHPUnit_Framework_TestCase {

    protected function createCache ($config = array()) {
        return new Cache($config);
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Config should be an array
     */
    public function testShouldBeReturnErrorIfMethodGenerateCallWithConfigDontArrayType () {
        $this->createCache((object)array());
    }

    public function testShouldMakeForgetProperty () {
        $cache = $this->createCache();
        $cache->setCachedTime(10);
        $cache->set('name1', 'value1');
        $this->assertEquals('value1', $cache->get('name1'), 'get value before forget test');
        $cache->expire('name1');
        $this->assertEquals(NULL, $cache->get('name1'), 'get value after forget test');
    }

}
