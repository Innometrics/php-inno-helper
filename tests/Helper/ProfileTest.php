<?php

namespace Helper;

require_once('vendor/autoload.php');

class ProfileTest extends Base {
    
    public function testShouldCreateProfile () {
        $helper = $this->createHelper();
        
        $profileId = 'profile-id';
        $profile = $helper->createProfile($profileId);
        
        $this->assertSame($profile->getId(), $profileId);
        $this->assertEquals($profile->getSessions(), array());
        $this->assertEquals($profile->getAttributes(), array());
        $this->assertFalse($profile->hasChanges());
    }
    
    public function testShouldMakePropertyRequestToLoadProfile () {
        $helper = $this->createHelper();
        
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
    
    public function testShouldReturnErrorIfOccurredWhileRequestToLoadOrDeleteProfile () {
        $helper = $this->createHelper();
        
        $httpCode = 500;
        $profileId = 'profile-id';
        $errorMsg = 'Something is wrong there';
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->any())
            ->will($this->returnValue(json_encode(array(
                'statusCode' => $httpCode,
                'message' => $errorMsg
            )))); 
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->any())
            ->will($this->returnValue($httpCode)); 
        
        foreach (array('loadProfile', 'deleteProfile') as $func) {
            try {
                $helper->{$func}($profileId);
            } catch (\Exception $ex) {
                $errMsg = sprintf('Server failed with status code %d: "%s"', $httpCode, $errorMsg);

                $this->assertEquals($errMsg, $ex->getMessage());
            }        
        }
    }
    
    public function testShouldReturnNullIfProfileDataCorruptedWhileRequestToLoadProfile () {
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
                
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
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        
        try {
            $helper->saveProfile(true);
        } catch (\Exception $e) {
            $this->stringStartsWith('Argument 1 passed to Innometrics\Helper::saveProfile() must be an instance of Innometrics\Profile', $e->getMessage());
        }
    }
    
    public function testShouldMakeProperlyRequestToSaveProfile () {
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        
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
    
    public function testShouldReturnProfileReceivedAfterSaveProfile () {
        $helper = $this->createHelper();
        
        $httpCode = 200;
        $profileId = 'profile-id';
        $profile = $helper->createProfile($profileId);
        
        $savedProfileId = 'saved-profile-id';
        
        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(json_encode(array(
                'profile' => array(
                    'id' => $savedProfileId
                )
            ))));
        
        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue($httpCode)); 
        
        $savedProfile = $helper->saveProfile($profile);
        
        $this->assertSame($savedProfile->getId(), $savedProfileId);
        $this->assertFalse($savedProfile->hasChanges());
    }
    
    public function testShouldReturnErrorIfProfileIsNotInstanceOfProfileWhileRequestToMergeProfiles () {
        $helper = $this->createHelper();
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
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        
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
        $this->assertFalse($savedProfile->hasChanges());
    }
    
    public function testShouldReturnErrorIfProfileIsNotInstanceOfProfileWhileRequestToRefreshProfile () {
        $helper = $this->createHelper();
        
        try {
            $helper->refreshLocalProfile(true);
        } catch (\Exception $e) {
            $this->stringStartsWith('Argument 1 passed to Innometrics\Helper::refreshLocalProfile() must be an instance of Innometrics\Profile', $e->getMessage());
        }
    }
    
    public function testShouldCallLoadProfileToRefreshProfile () {
        $config = $this->config;
        $profileId = 'profile-id';
        
        $helper = \Mockery::mock('Innometrics\Helper[loadProfile]', array(
            $config
        ))->makePartial();
        
        $helper
            ->shouldReceive('loadProfile')
            ->with($profileId)
            ->once()
            ->andReturnUsing(function ($_profileId) use ($helper) {
                return $helper->createProfile($_profileId);
            });        
            
        $profile = $helper->createProfile($profileId);
        $profile = $helper->refreshLocalProfile($profile);
        
        $this->assertInstanceOf('Innometrics\Profile', $profile);
        $this->assertEquals($profileId, $profile->getId());
    }
    
    public function testShouldCallMergeProfileToRefreshProfile () {
        $config = $this->config;
        $profileId = 'profile-id';
        
        $helper = \Mockery::mock('Innometrics\Helper[loadProfile]', array(
            $config
        ))->makePartial();
        
        $helper
            ->shouldReceive('loadProfile')
            ->with($profileId)
            ->once()
            ->andReturnUsing(function ($_profileId) use ($helper) {
                return $helper->createProfile($_profileId);
            });
        
        $profile = \Mockery::mock('Innometrics\Profile', array(
            array(
                'id' => $profileId
            )
        ))->makePartial();
        
        $profile
            ->shouldReceive('merge')
            ->andReturnUsing(function ($profile) {
                return $profile;
            });
        
        $profile = $helper->refreshLocalProfile($profile);
        
        $this->assertInstanceOf('Innometrics\Profile', $profile);
        $this->assertEquals($profileId, $profile->getId());
    }
    
    public function testShouldReturnErrorIfOccurredWhileRequestToRefreshProfile () {
        $profileId = 'profile-id';
        $httpCode = 500;
        $errorMsg = 'Something is wrong there';
        
        $helper = $this->createHelper();
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
        $helper = $this->createHelper();
        $helper->getProfileFromRequest('abc');
    }
    
    public function testShouldProperlyCreateProfileFromProfileStream () {
        $profileId = 'profile-id';
        $jsonBody = array(
            'profile' => array(
                'id' => $profileId
            )
        );
        
        $helper = $this->createHelper();
        
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
        $helper = $this->createHelper();
        $helper->getMetaFromRequest('abc');
    }
    
    public function testShouldProperlyReceiveMetaFromProfileStream () {
        $helper = $this->createHelper();
        
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
