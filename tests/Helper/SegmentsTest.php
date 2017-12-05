<?php

namespace Helper;

require_once('vendor/autoload.php');

require_once 'Base.php';

use Innometrics\Segment;

class SegmentsTest extends Base {

    public function testShouldMakeProperlyRequestToGetSettings () {
        $helper = $this->createHelper();

        $curlConfig = array();

        $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
        $curlExecMock->expects($this->once())
            ->will($this->returnValue(''));

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->once())
            ->will($this->returnValue(200));

        $curlSetoptMock = new \PHPUnit_Extensions_MockFunction('curl_setopt', $helper);
        $curlSetoptMock->expects($this->any())
            ->will($this->returnCallback(function($curl, $optName, $optValue) use (&$curlConfig) {
                $curlConfig[$optName] = $optValue;
            }));

        // Request to server
        $helper->getSegments();

        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'apiUrl/v1/companies/4/buckets/bucketName/segments?app_key=appKey';
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
    public function testShouldErrorIfOccurredWhileGettingSegments () {
        $helper = $this->createHelper();
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

        $helper->getSegments();
    }

    public function testShouldReturnArrayOfSegments () {
        $helper = $this->createHelper();

        $httpCode = 200;

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->any())
            ->will($this->returnValue($httpCode));

        $bodies = array(
            array(
                'body' => array(
                    array(
                        'segment' => array(
                            'id' => '1',
                            'iql' => 'test1'
                        )
                    ),
                    array(
                        'segment' => array(
                            'id' => '1',
                            'noiql' => 'HAHA'
                        )
                    ),
                    array(
                        'yes' => 'Segment'
                    ),
                    true
                ),
                'count' => 1
            ),
            array(
                'body' => array(
                    'array' => 'data'
                ),
                'count' => 0
            )
        );

        foreach ($bodies as $body) {
            $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
            $curlExecMock->expects($this->once())
                ->will($this->returnValue(json_encode($body['body'])));

            $segments = $helper->getSegments();

            $this->assertTrue(is_array($segments));
            $this->assertEquals($body['count'], count($segments));

            foreach ($segments as $sgm) {
                $this->assertInstanceOf('Innometrics\Segment', $sgm);
            }
        }
    }

    public function testShouldReturnErrorIfInstanceErrorsWhileRequestToEvalProfile () {
        $helper = $this->createHelper();
        $profile = $helper->createProfile('profile-id');

        try {
            $helper->evaluateProfileBySegment($profile, true);
        } catch (\Exception $e) {
            $this->assertStringStartsWith('Argument 2 passed to Innometrics\Helper::evaluateProfileBySegment() must be an instance of Innometrics\Segment', $e->getMessage());
        }

        try {
            $helper->evaluateProfileBySegment(true, true);
        } catch (\Exception $e) {
            $this->assertStringStartsWith('Argument 1 passed to Innometrics\Helper::evaluateProfileBySegment() must be an instance of Innometrics\Profile', $e->getMessage());
        }
    }

    public function testShouldDelegateEvaluationFromProfileBySegment () {
        $config = $this->config;

        $helper = \Mockery::mock('Innometrics\Helper[evaluateProfileBySegmentId]', array(
            $config
        ))->makePartial();

        $segment = new Segment(array(
            'id' => "1",
            'iql' => 'my-iql'
        ));
        $profile = $helper->createProfile('profile-id');
        $helper
            ->shouldReceive('evaluateProfileBySegmentId')
            ->with($profile, $segment->getId())
            ->once()
            ->andReturnUsing(function () {
                return true;
            });

        $helper->evaluateProfileBySegment($profile, $segment);
    }

    public function testShouldDelegateEvaluationFromProfileByIql () {
        $config = $this->config;

        $helper = $this->getMock('Innometrics\Helper', array('_evaluateProfileByParams'), array($config));

        $profile = $helper->createProfile('profile-id');

        $segmentId = '1';

        $helper
            ->expects($this->once())
            ->method('_evaluateProfileByParams')
            ->with($this->equalTo($profile), $this->equalTo(array(
                'segment_id' => array($segmentId),
                'typeSegmentEvaluation' => 'segment-id-evaluation'
            )))
            ->will($this->returnValue(false));

        $res = $helper->evaluateProfileBySegmentId($profile, $segmentId);

        $this->assertFalse($res);
    }

    public function testShouldDelegateEvaluationFromProfileBySegmentId () {
        $config = $this->config;

        $helper = $this->getMock('Innometrics\Helper', array('_evaluateProfileByParams'), array($config));

        $profile = $helper->createProfile('profile-id');

        $segmentIql = 'my-iql';

        $helper
            ->expects($this->once())
            ->method('_evaluateProfileByParams')
            ->with($this->equalTo($profile), $this->equalTo(array(
                'iql' => $segmentIql,
                'typeSegmentEvaluation' => 'iql-evaluation'
            )))
            ->will($this->returnValue(true));

        $res = $helper->evaluateProfileByIql($profile, $segmentIql);

        $this->assertTrue($res);
    }

    public function testShouldMakeProperlyRequestToEvalProfile () {
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

        $evalParams = array(
            'some' => 'params',
            'are' => 'here',
            'typeSegmentEvaluation' => 'segment-id-evaluation'
        );

        try {
            $method = new \ReflectionMethod('Innometrics\Helper', '_evaluateProfileByParams');
            $method->setAccessible(true);
            $method->invoke($helper, $profile, $evalParams);
        } catch (\Exception $ex) {
            $this->assertEquals('Unknown error', $ex->getMessage());
        }

        $curlReferenceConfig = array();
        $curlReferenceConfig[CURLOPT_URL] = 'evaluationApiUrl/companies/4/buckets/bucketName/segment-id-evaluation?app_key=appKey&some=params&are=here&profile_id=' . $profileId;
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

    public function testShouldReturnErrorIfOccurredWhileRequestToEvalProfile () {
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
            $method = new \ReflectionMethod('Innometrics\Helper', '_evaluateProfileByParams');
            $method->setAccessible(true);
            $method->invoke($helper, $profile, array(
                'segment_id' => '1',
                'typeSegmentEvaluation' => 'segment-id-evaluation'
            ));
        } catch (\Exception $ex) {
            $errMsg = sprintf('Server failed with status code %d: "%s"', $httpCode, $errorMsg);

            $this->assertEquals($errMsg, $ex->getMessage());
        }
    }

    public function testShouldReturnEvalProfileResult () {
        $helper = $this->createHelper();

        $httpCode = 200;
        $profile = $helper->createProfile();

        $curlGetinfoMock = new \PHPUnit_Extensions_MockFunction('curl_getinfo', $helper);
        $curlGetinfoMock->expects($this->any())
            ->will($this->returnValue($httpCode));

        $bodies = array(
            array(
                'body' => array(
                    'segmentEvaluation' => array(
                        'noresult' => 'here'
                    )
                ),
                'results' => null
            ),
            array(
                'body' => array(
                    'segmentEvaluation' => array(
                        'results' => array('some result')
                    )
                ),
                'results' => 'some result'
            )
        );

        foreach ($bodies as $body) {
            $curlExecMock = new \PHPUnit_Extensions_MockFunction('curl_exec', $helper);
            $curlExecMock->expects($this->once())
                ->will($this->returnValue(json_encode($body['body'])));

            $method = new \ReflectionMethod('Innometrics\Helper', '_evaluateProfileByParams');
            $method->setAccessible(true);
            $res = $method->invoke($helper, $profile, array(
                'segment_id' => '1',
                'typeSegmentEvaluation' => 'segment-id-evaluation'
            ));

            $this->assertEquals($body['results'], $res);
        }
    }

}
