<?php

namespace Helper;

require_once('vendor/autoload.php');

class ProfileTest extends Base {
    
    public function testShouldCreateProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $profileId = 'profile-id';
        $profile = $helper->createProfile($profileId);
        
        $this->assertSame($profile->getId(), $profileId);
        $this->assertEquals($profile->getSessions(), array());
        $this->assertEquals($profile->getAttributes(), array());
    }
    
    public function testShouldMakePropertyRequestToLoadProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $curlConfig = array();
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(false));        
        
        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $helper);
        $curlSetoptMock->expects($this->any())
            ->will($this->returnCallback(function($curl, $optName, $optValue) use (&$curlConfig) {
                $curlConfig[$optName] = $optValue;
            }));
        
        $profileId = 'profile-id';
        
        try {
            $helper->loadProfile($profileId);
        } catch (\Exception $ex) {
            $this->assertEquals('Unknown error', $ex->getMessage());
        }
        
        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'apiUrl/v1/companies/4/buckets/bucketName/profiles/' . $profileId . '?app_key=appKey';
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
    
    public function testShouldReturnErrorIfOccurredWhileRequestToLoadProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 500;
        $profileId = 'profile-id';
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
        
        try {
            $helper->loadProfile($profileId);
        } catch (\Exception $ex) {
            $errMsg = sprintf('Server failed with status code %d: "%s"', $httpCode, $errorMsg);
            
            $this->assertEquals($errMsg, $ex->getMessage());
        }        
    }
    
    public function testShouldReturnNullIfProfileDataCorruptedWhileRequestToLoadProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 200;
        $profileId = 'profile-id';
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->any())
            ->will($this->returnValue($httpCode));
        
        $bodies = array(
            array(),
            array(
                'profile' => true
            )
        );
        
        foreach ($bodies as $body) {
            $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
            $curlExecMock->expects($this->once())
                ->will($this->returnValue(json_encode($body))); 
            
            $profile = $helper->loadProfile($profileId);
            $this->assertNull($profile);
        }
    }
    
    public function testShouldReturnProfileWhileRequestToLoadProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 200;
        $profileId = 'profile-id';
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode));
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'profile' => array(
                    'id' => $profileId
                )
            )))); 
            
        $profile = $helper->loadProfile($profileId);
        $this->assertInstanceOf('Innometrics\Profile', $profile);
        $this->assertEquals($profileId, $profile->getId());
    }
    
    public function testShouldMakeProperlyRequestToDeleteProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
                
        $curlConfig = array();
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(false));        
        
        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $helper);
        $curlSetoptMock->expects($this->any())
            ->will($this->returnCallback(function($curl, $optName, $optValue) use (&$curlConfig) {
                $curlConfig[$optName] = $optValue;
            }));
        
        $profileId = 'profile-id';
        
        try {
            $helper->deleteProfile($profileId);
        } catch (\Exception $ex) {
            $this->assertEquals('Unknown error', $ex->getMessage());
        }
        
        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'apiUrl/v1/companies/4/buckets/bucketName/profiles/' . $profileId . '?app_key=appKey';
        $curlReferenceConfig[CURLOPT_CUSTOMREQUEST] = 'delete';
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
    
    /*
    describe('Delete', function () {

        it('should return error if occurred while request', function (done) {
            sinon.stub(request, 'del', function (opts, callback) {
                callback(new Error('request error'));
            });
            helper.deleteProfile('pid', function (error) {
                assert(error);
                assert(error.message, 'request error');
                request.del.restore();
                done();
            });
        });

        it('should not return error if success', function (done) {
            sinon.stub(request, 'del', function (opts, callback) {
                var response = {
                    statusCode: 204,
                    body: {}
                };
                callback(null, response);
            });
            helper.deleteProfile('pid', function (error) {
                assert.ifError(error);
                request.del.restore();
                done();
            });
        });

    });    
     */
}
