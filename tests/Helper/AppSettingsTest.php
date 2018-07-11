<?php

namespace Helper;

require_once('vendor/autoload.php');

require_once 'Base.php';

class AppSettingsTest extends Base {
    
    public function testShouldMakeProperlyRequestToGetSettings () {
        $helper = $this->createHelper();
        $values = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );
       
        $curlConfig = array();
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'custom' => $values
            ))));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));

        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $this);
        $curlSetoptMock->expects($this->any())
            ->will($this->returnCallback(function($curl, $optName, $optValue) use (&$curlConfig) {
                $curlConfig[$optName] = $optValue;
            }));

        // Request to server
        $helper->getAppSettings();

        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'apiUrl/v1/companies/4/buckets/bucketName/apps/appName/custom?app_key=appKey';
        $curlReferenceConfig[CURLOPT_RETURNTRANSFER] = true;
        $curlReferenceConfig[CURLOPT_HTTPHEADER] = array(
            "Content-Type: application/json",
            "Accept: application/json"
        );

        ksort($curlConfig);
        ksort($curlReferenceConfig);

        $this->assertEquals(
            $curlConfig,
            $curlReferenceConfig
        );
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Server failed with status code 500: "Something is wrong there"
     */
    public function testShouldErrorIfOccurredWhileGettingSettings () {
        $helper = $this->createHelper();
        $httpCode = 500;
        $errorMsg = 'Something is wrong there';

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'statusCode' => $httpCode,
                'message' => $errorMsg
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode));

        $helper->getAppSettings();
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Custom settings not found
     */
    public function testShouldErrorIfNoCustomFieldWhileGettingSettings () {
        $helper = $this->createHelper();

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'no-custom' => 1
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));

        $helper->getAppSettings();
    }

    public function testShouldUseCacheWhileGettingSettings () {
        $helper = $this->createHelper();
        $values = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'custom' => $values
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));

        // Request to server
        $settings1 = $helper->getAppSettings();
        $this->assertEquals($values, $settings1);

        // From cache
        $settings2 = $helper->getAppSettings();
        $this->assertEquals($values, $settings2);
    }

    public function testShouldRequestToServerIfNoCacheAllowedWhileGettingSettings () {
        $helper = $this->createHelper();
        $helper->setCacheAllowed(false);
        $values = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->exactly(2))
            ->will($this->returnValue(json_encode(array(
                'custom' => $values
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->exactly(2))
            ->will($this->returnValue(200));

        // Request to server
        $settings1 = $helper->getAppSettings();
        $this->assertEquals($values, $settings1);

        // From cache
        $settings2 = $helper->getAppSettings();
        $this->assertEquals($values, $settings2);
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Settings should be an array
     */
    public function testShouldThrowErrorIfNoSettingsWhileSetting () {
        $helper = $this->createHelper();
        $helper->setAppSettings(null);
    }

    public function testShouldMakeProperlyRequestToSetSettings () {
        $helper = $this->createHelper();
        $values = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );

        $curlConfig = array();

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'custom' => $values
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));

        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $this);
        $curlSetoptMock->expects($this->any())
            ->will($this->returnCallback(function($curl, $optName, $optValue) use (&$curlConfig) {
                $curlConfig[$optName] = $optValue;
            }));

        // Request to server
        $helper->setAppSettings($values);

        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'apiUrl/v1/companies/4/buckets/bucketName/apps/appName/custom?app_key=appKey';
        $curlReferenceConfig[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $curlReferenceConfig[CURLOPT_RETURNTRANSFER] = true;
        $curlReferenceConfig[CURLOPT_POSTFIELDS] = json_encode($values);
        $curlReferenceConfig[CURLOPT_HTTPHEADER] = array(
            "Content-Type: application/json",
            "Accept: application/json"
        );

        ksort($curlConfig);
        ksort($curlReferenceConfig);

        $this->assertSame(
            $curlConfig,
            $curlReferenceConfig
        );
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Server failed with status code 500: "Something is wrong there"
     */
    public function testShouldErrorIfOccurredWhileSettingSettings () {
        $helper = $this->createHelper();
        $httpCode = 500;
        $errorMsg = 'Something is wrong there';

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'statusCode' => $httpCode,
                'message' => $errorMsg
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode));

        $helper->setAppSettings(array('one' => 'two'));
    }

    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Custom settings not found
     */
    public function testShouldErrorIfNoCustomFieldWhileSettingSettings () {
        $helper = $this->createHelper();

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'no-custom' => 1
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));

        $helper->setAppSettings(array('one' => 'two'));
    }

    public function testShouldReturnSettingsAndSetCacheIfAllowedWhileSettingSettings () {
        $helper = $this->createHelper();
        $values = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $this);
        $curlExecMock->expects($this->any())
            ->will($this->returnValue(json_encode(array(
                'custom' => $values
            ))));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $this);
        $curlGetinfoMock->expects($this->any())
            ->will($this->returnValue(200));

        $settings1 = $helper->setAppSettings(array());
        $this->assertEquals($values, $settings1);

        $cacheMethod = new \ReflectionMethod('Innometrics\Helper', 'getCacheKey');
        $cacheMethod->setAccessible(true);
        $cacheKey = $cacheMethod->invoke($helper, 'settings');

        $cacheProp = new \ReflectionProperty('Innometrics\Helper', 'cache');
        $cacheProp->setAccessible(true);
        $cache = $cacheProp->getValue($helper);
        $this->assertEquals($values, $cache->get($cacheKey));

        $cache->clearCache();
        $helper->setCacheAllowed(false);

        $settings2 = $helper->setAppSettings(array());
        $this->assertEquals($values, $settings2);
        $this->assertNull($cache->get($cacheKey));
    }

}
