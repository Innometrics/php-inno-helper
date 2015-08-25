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
    
    /**
     * TODO: loadProfile + deleteProfile
     */
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
    
    public function testShouldNotReturnErrorIfSuccessToDeleteProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 204;
        $profileId = 'profile-id';
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode));
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue('')); 
            
        $success = $helper->deleteProfile($profileId);
        
        $this->assertTrue($success);
    }
    
    public function testShouldReturnErrorIfProfileIsNotInstanceOfProfileWhileRequestToSaveProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        try {
            $helper->saveProfile(true);
        } catch (\Exception $e) {
            $this->stringStartsWith('Argument 1 passed to Innometrics\Helper::saveProfile() must be an instance of Innometrics\Profile', $e->getMessage());
        }
    }
    
    public function testShouldMakeProperlyRequestToSaveProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $profileId = 'profile-id';
        $profile = $helper->createProfile($profileId);        
                
        $curlConfig = array();
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(false));        
        
        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $helper);
        $curlSetoptMock->expects($this->any())
            ->will($this->returnCallback(function($curl, $optName, $optValue) use (&$curlConfig) {
                $curlConfig[$optName] = $optValue;
            }));
        
        try {
            $helper->saveProfile($profile);
        } catch (\Exception $ex) {
            $this->assertEquals('Unknown error', $ex->getMessage());
        }
        
        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'apiUrl/v1/companies/4/buckets/bucketName/profiles/' . $profileId . '?app_key=appKey';
        $curlReferenceConfig[CURLOPT_CUSTOMREQUEST] = 'post';
        $curlReferenceConfig[CURLOPT_RETURNTRANSFER] = true;
        $curlReferenceConfig[CURLOPT_POSTFIELDS] = '{"id":"' . $profileId . '","attributes":[],"sessions":[]}';
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
    
    public function testShouldReturnErrorIfOccurredWhileRequestToSaveProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 500;
        $profileId = 'profile-id';
        $profile = $helper->createProfile($profileId);
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
            $helper->saveProfile($profile);
        } catch (\Exception $ex) {
            $errMsg = sprintf('Server failed with status code %d: "%s"', $httpCode, $errorMsg);
            
            $this->assertEquals($errMsg, $ex->getMessage());
        }
    }
    
    public function testShouldReturnSameProfileIfNoDataWhileRequestToSaveProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 200;
        $profileId = 'profile-id';
        $profile = $helper->createProfile($profileId);
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(''));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode)); 
        
        $savedProfile = $helper->saveProfile($profile);
        
        $this->assertSame($profile, $savedProfile);
    }
    
    public function testShouldReturnErrorIfProfileIsNotInstanceOfProfileWhileRequestToMergeProfiles () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        $profile = $helper->createProfile('profile-id');
        
        try {
            $helper->mergeProfiles(true, $profile);
        } catch (\Exception $e) {
            $this->stringStartsWith('Argument 1 passed to Innometrics\Helper::mergeProfiles() must be an instance of Innometrics\Profile', $e->getMessage());
        }
        
        try {
            $helper->mergeProfiles($profile, array());
        } catch (\Exception $e) {
            $this->stringStartsWith('Argument 2 passed to Innometrics\Helper::mergeProfiles() must be an instance of Innometrics\Profile', $e->getMessage());
        }
    }
    
    public function testShouldMakeProperlyRequestToMergeProfiles () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $profileId1 = 'profile-id-1';
        $profileId2 = 'profile-id-2';
        $profile1 = $helper->createProfile($profileId1);        
        $profile2 = $helper->createProfile($profileId2);        
                
        $curlConfig = array();
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(false));        
        
        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $helper);
        $curlSetoptMock->expects($this->any())
            ->will($this->returnCallback(function($curl, $optName, $optValue) use (&$curlConfig) {
                $curlConfig[$optName] = $optValue;
            }));
        
        try {
            $helper->mergeProfiles($profile1, $profile2);
        } catch (\Exception $ex) {
            $this->assertEquals('Unknown error', $ex->getMessage());
        }
        
        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'apiUrl/v1/companies/4/buckets/bucketName/profiles/' . $profileId1 . '?app_key=appKey';
        $curlReferenceConfig[CURLOPT_CUSTOMREQUEST] = 'post';
        $curlReferenceConfig[CURLOPT_RETURNTRANSFER] = true;
        $curlReferenceConfig[CURLOPT_POSTFIELDS] = '{"id":"' . $profileId1 . '","mergedProfiles":["' . $profileId2 . '"]}';
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
    
    public function testShouldReturnErrorIfOccurredWhileRequestToMergeProfiles () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 500;
        $profileId1 = 'profile-id-1';
        $profileId2 = 'profile-id-2';
        $profile1 = $helper->createProfile($profileId1);        
        $profile2 = $helper->createProfile($profileId2);   
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
            $helper->mergeProfiles($profile1, $profile2);
        } catch (\Exception $ex) {
            $errMsg = sprintf('Server failed with status code %d: "%s"', $httpCode, $errorMsg);
            
            $this->assertEquals($errMsg, $ex->getMessage());
        }
    }   
    
    public function testShouldReturnNullIfNoDataWhileRequestToMergeProfiles () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 200;
        $profileId1 = 'profile-id-1';
        $profileId2 = 'profile-id-2';
        $profile1 = $helper->createProfile($profileId1);        
        $profile2 = $helper->createProfile($profileId2); 
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(''));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode)); 
        
        $savedProfile = $helper->mergeProfiles($profile1, $profile2);
        
        $this->assertNull($savedProfile);
    }    
    
    public function testShouldReturnProfileWhileRequestToMergeProfiles () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $httpCode = 200;
        $profileId1 = 'profile-id-1';
        $profileId2 = 'profile-id-2';
        $profile1 = $helper->createProfile($profileId1);        
        $profile2 = $helper->createProfile($profileId2); 
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'profile' => array(
                    'id' => $profileId1
                )
            ))));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode)); 
        
        $savedProfile = $helper->mergeProfiles($profile1, $profile2);
        
        $this->assertInstanceOf('Innometrics\Profile', $savedProfile);
        $this->assertEquals($profileId1, $savedProfile->getId());
    }
    
    public function testShouldReturnErrorIfProfileIsNotInstanceOfProfileWhileRequestToRefreshProfile () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        try {
            $helper->refreshLocalProfile(true);
        } catch (\Exception $e) {
            $this->stringStartsWith('Argument 1 passed to Innometrics\Helper::refreshLocalProfile() must be an instance of Innometrics\Profile', $e->getMessage());
        }
    }
    
    public function testShouldReturnErrorIfOccurredWhileRequestToRefreshProfile () {
        $config = $this->config;
        $profileId = 'profile-id';
        $httpCode = 500;
        $errorMsg = 'Something is wrong there';
        
        $helper = $this->createHelper($config);
        $profile = $helper->createProfile($profileId);
        
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
            $helper->refreshLocalProfile($profile);
        } catch (\Exception $e) {
            $this->stringStartsWith('Server failed with status code 500: "' . $errorMsg . '"', $e->getMessage());
        }
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Profile not found
     */    
    public function testShouldThrowErrorOnWrongDataInProfileStream () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        $helper->getProfileFromRequest('abc');
    }
    
    public function testShouldProperlyCreateProfileFromProfileStream () {
        $config = $this->config;
        $profileId = 'profile-id';
        $jsonBody = array(
            'profile' => array(
                'id' => $profileId
            )
        );
        
        $helper = $this->createHelper($config);
        
        $profile1 = $helper->getProfileFromRequest($jsonBody);
        $this->assertEquals($profile1->getId(), $profileId);
        
        $profile2 = $helper->getProfileFromRequest(json_encode($jsonBody));
        $this->assertEquals($profile2->getId(), $profileId);
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Meta not found
     */    
    public function testShouldThrowErrorOnWrongMetaDataInProfileStream () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        $helper->getMetaFromRequest('abc');
    }
    
    public function testShouldProperlyReceiveMetaFromProfileStream () {
        $config = $this->config;
        $helper = $this->createHelper($config);
        
        $meta = array(
            'some' => 'data',
            'other' => 'data'
        );
        
        $jsonBody = array(
            'meta' => $meta
        );
        
        $this->assertSame($helper->getMetaFromRequest($jsonBody), $meta);
        $this->assertSame($helper->getMetaFromRequest(json_encode($jsonBody)), $meta);
    }    
    
}
