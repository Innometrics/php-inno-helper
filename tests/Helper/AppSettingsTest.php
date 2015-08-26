<?php

namespace Helper;

require_once('vendor/autoload.php');

require_once 'Base.php';

class AppSettingsTest extends Base {
    
    public function testShouldMakeProperlyRequestToGetSettings () {
        $config = $this->config;
        $helper = $this->createHelper($config);  
        $values = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );
        
        $curlConfig = array();
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'custom' => $values
            ))));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));  
        
        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $helper);
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
        
        $this->assertSame(
            $curlConfig,
            $curlReferenceConfig
        );
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Server failed with status code 500: "Something is wrong there"
     */     
    public function testShouldErrorIfOccurredWhileGettingSettings () {
        $config = $this->config;
        $helper = $this->createHelper($config); 
        $httpCode = 500;
        $errorMsg = 'Something is wrong there';
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'statusCode' => $httpCode,
                'message' => $errorMsg
            ))));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode));        
        
        $helper->getAppSettings();
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Custom settings not found
     */      
    public function testShouldErrorIfNoCustomFieldWhileGettingSettings () {
        $config = $this->config;
        $helper = $this->createHelper($config);  
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'no-custom' => 1
            ))));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));        
        
        $helper->getAppSettings();
    } 
    
    public function testShouldUseCacheWhileGettingSettings () {
        $config = $this->config;
        $helper = $this->createHelper($config);  
        $values = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'custom' => $values
            ))));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));        
        
        // Request to server
        $settings1 = $helper->getAppSettings();
        $this->assertEquals($settings1, $values);
        
        // From cache
        $settings2 = $helper->getAppSettings();
        $this->assertEquals($settings2, $values);
    }

    /*

        it('should return settings from server if nocache=true', function (done) {
            var values = ['settings', 'here'];
            sinon.stub(request, 'get', function (opts, callback) {
                callback(null, {statusCode: 200, body: {custom: values}});
            });

            helper.setCacheAllowed(false);
            helper.getAppSettings(function (error, settings) {
                assert.ifError(error);
                assert.deepEqual(settings, values);
                assert(request.get.called);

                helper.getAppSettings(function (error, settings) {
                    assert.ifError(error);
                    assert.deepEqual(settings, values);
                    assert(request.get.called);

                    request.get.restore();
                    done();
                });
            });
        });

        it('should throw error if no settings passed', function (done) {

            helper.setAppSettings(null, function (error) {
                assert(error);
                assert.equal(error.message, 'Settings not found');
                done();
            });
        });

        it('should make properly request to set application settings', function (done) {
            var settings = {test: 'qwe'};

            sinon.stub(request, 'put', function (opts, callback) {
                callback();
            });

            helper.setAppSettings(settings, function () {
                assert(request.put.calledWith({
                    url: 'apiUrl/v1/companies/4/buckets/bucketName/apps/appName/custom?app_key=appKey',
                    body: settings,
                    json: true
                }));
                request.put.restore();
                done();
            });
        });

        it('should return error if occurred while request', function (done) {
            sinon.stub(request, 'put', function (opts, callback) {
                callback(new Error('request error'));
            });
            helper.setAppSettings({}, function (error) {
                assert(error);
                assert(error.message, 'request error');
                request.put.restore();
                done();
            });
        });

        it('should return error if "custom" field not found', function (done) {
            sinon.stub(request, 'put', function (opts, callback) {
                callback(null, {statusCode: 200, body: {no: 'custom'}});
            });
            helper.setAppSettings({}, function (error) {
                assert(error);
                assert(error.message, 'CUSTOM not found');
                request.put.restore();
                done();
            });
        });

        it('should return settings and set cache if allowed', function (done) {
            var values = ['settings', 'here'];
            sinon.stub(request, 'put', function (opts, callback) {
                callback(null, {statusCode: 200, body: {custom: values}});
            });
            helper.setAppSettings({}, function (error, settings) {
                assert.ifError(error);
                assert.deepEqual(settings, values);
                assert.deepEqual(helper.cache.get(helper.getCacheKey('settings')), values);

                helper.cache.clearCache();
                helper.setCacheAllowed(false);
                helper.setAppSettings({}, function (error, settings) {
                    assert.ifError(error);
                    assert.deepEqual(settings, values);
                    assert.strictEqual(helper.cache.get(helper.getCacheKey('settings')), undefined);

                    request.put.restore();
                    done();
                });
            });
        });
    
     */
    
    
    
    
    
    
    
    
    
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
        $helper = $this->createHelper($config);
        
        $this->assertSame($helper->getBucket(), $config['bucketName']);
        $this->assertSame($helper->getCollectApp(), $config['appName']);
        $this->assertSame($helper->getAppKey(), $config['appKey']);
        $this->assertSame($helper->getApiHost(), $config['apiUrl']);
        $this->assertSame($helper->getCompany(), $config['groupId']);
    }
    
    public function testShouldGenerateUrlsCorrectly () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
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
