<?php

namespace Helper;

require_once('vendor/autoload.php');
require_once 'Base.php';

use Innometrics\Helper;

class HelperTest extends Base {

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Config should be a non-empty array
     */     
    public function testShouldThrowErrorOnEmptyConfig () {
//        $this->createHelper();
        new Helper();
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Config should be a non-empty array
     */    
    public function testShouldThrowErrorOnNonArrayConfig () {
        $this->createHelper(true);
    }
    
    public function testShouldThrowErrorOnNonSufficientConfig () {
        
        $configs = array(
            array(
                'field' => 'appName',
                'initConf' => array('bucketName' => 'bucketName')
            ),
            array(
                'field' => 'appKey',
                'initConf' => array('bucketName' => 'bucketName', 'appName' => 'appName')
            ),
            array(
                'field' => 'apiUrl',
                'initConf' => array('bucketName' => 'bucketName', 'appName' => 'appName', 'appKey' => 'appKey')
            ),
            array(
                'field' => 'groupId',
                'initConf' => array('bucketName' => 'bucketName', 'appName' => 'appName', 'appKey' => 'appKey', 'apiUrl' => 'apiUrl'),
                'wrongTypeExMsg' => 'Property "groupId" in config should be a string or a number'
            )
        );
        
        foreach ($configs as $config) {
            $initConf = $config['initConf'];
            $field = $config['field'];
            
            try {
                $this->createHelper($initConf);
            } catch (\ErrorException $ex) {
                $this->assertEquals('Property "' . $field . '" in config should be defined', $ex->getMessage());
            }
            
            try {
                $initConf[$field] = true;
                $this->createHelper($initConf);
            } catch (\ErrorException $ex) {
                $this->assertEquals(isset($config['wrongTypeExMsg']) ? $config['wrongTypeExMsg'] : 'Property "' . $field . '" in config should be a string', $ex->getMessage());
            }
            
            try {
                $initConf[$field] = '        ';
                $this->createHelper($initConf);
            } catch (\ErrorException $ex) {
                $this->assertEquals('Property "' . $field . '" in config can not be empty', $ex->getMessage());
            }
        }
    }
    
    public function testShouldNotThrowErrorOnCorrectConfig () {
        $this->createHelper(array(
            'bucketName' => 'bucketName',
            'appName' => 'appName',
            'appKey' => 'appKey',
            'apiUrl' => 'apiUrl',
            'groupId' => 4
        ));
        
        $this->createHelper(array(
            'bucketName' => 'bucketName',
            'appName' => 'appName',
            'appKey' => 'appKey',
            'apiUrl' => 'apiUrl',
            'groupId' => '42'
        ));
    }
    
    public function testShouldProperlyGetConfig () {
        $config = $this->config;
        $helper = $this->createHelper();
        
        $this->assertSame($helper->getBucket(), $config['bucketName']);
        $this->assertSame($helper->getCollectApp(), $config['appName']);
        $this->assertSame($helper->getAppKey(), $config['appKey']);
        $this->assertSame($helper->getApiHost(), $config['apiUrl']);
        $this->assertSame($helper->getCompany(), $config['groupId']);
    }
    
    public function testShouldGenerateUrlsCorrectly () {
        $helper = $this->createHelper();
        
        $configs = array(
            array(
                'method' => 'getProfileUrl',
                'arg'    => 'some-profile',
                'res'    => 'apiUrl/v1/companies/4/buckets/bucketName/profiles/some-profile?app_key=appKey'
            ),
            array(
                'method' => 'getAppSettingsUrl',
                'arg'    => '',
                'res'    => 'apiUrl/v1/companies/4/buckets/bucketName/apps/appName/custom?app_key=appKey'
            ),
            array(
                'method' => 'getSegmentsUrl',
                'arg'    => '',
                'res'    => 'apiUrl/v1/companies/4/buckets/bucketName/segments?app_key=appKey'
            ),
            array(
                'method' => 'getSegmentEvaluationUrl',
                'arg'    => array('param1' => 'value1'),
                'res'    => 'apiUrl/v1/companies/4/buckets/bucketName/segment-evaluation?app_key=appKey&param1=value1'
            ),
        );
        
        foreach ($configs as $config) {
            $method = new \ReflectionMethod('Innometrics\Helper', $config['method']);
            $method->setAccessible(true);
            $res = $method->invoke($helper, $config['arg']);

            $this->assertSame(
                $res,
                $config['res'],
                'should return ' . $config['method']
            );
        }
    }
}
