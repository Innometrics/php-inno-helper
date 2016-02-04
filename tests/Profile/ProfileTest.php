<?php

namespace Profile;

require_once('vendor/autoload.php');

class ProfileTest extends Base {
    
    protected $profileData = array(
        'id' => 'pid',
        'attributes' => array(
            array(
                'collectApp' => 'app1',
                'section' => 'sec1',
                'data' => array(
                    'foo' => 'bar',
                    'test' => 1
                )
            ), array(
                'collectApp' => 'app2',
                'section' => 'sec2',
                'data' => array(
                    'cat' => 'dog',
                    'hi' => 'bye'
                )
            )
        ),
        'sessions' => array(
            array(
                'id' => 'sid1',
                'collectApp' => 'app1',
                'section' => 'sec1',
                'createdAt' => 1442476047267,
                'modifiedAt' => 1442476047267,
                'data' => array(
                    'data1' => 'value1'
                ),
                'events' => array()
            ), 
            array(
                'id' => 'sid2',
                'collectApp' => 'app2',
                'section' => 'sec2',
                'createdAt' => 1442476047267,
                'modifiedAt' => 1442476047267,
                'data' => array(),
                'events' => array(
                    array(
                        'id' => 'ev1',
                        'definitionId' => 'def1',
                        'createdAt' => 1442476047267,
                        'data' => array(
                            'spider' => 'man'
                        )
                    )
                )
            ), 
            array(
                'id' => 'sid3',
                'collectApp' => 'app3',
                'section' => 'sec3',
                'createdAt' => 1442476047267,
                'modifiedAt' => 1442476047267,
                'data' => array(),
                'events' => array()
            )
        )
    );
    
    /**
     * TODO: should be splitted
     */
    public function testShouldReturnSomeData () {
        $profile = $this->createProfile(array(
            'id' => 'pid',
            'attributes' => array(
                array(
                    'collectApp' => 'collectApp1',
                    'section' => 'section1',
                    'data' => array('name1' => 'value1')
                ),
                array(
                    'collectApp' => 'collectApp2',
                    'section' => 'section2',
                    'data' => array('name2' => 'value2')
                )
            ),
            'sessions' => array(
                array(
                    'id' => 'sid',
                    'collectApp' => 'collectApp1',
                    'section' => 'section1',
                    'data' => array('name1' => 'value1'),
                    'events' => array(
                        array(
                            'definitionId' => 'name1',
                            'data' => array('name1' => 'value1')
                        )
                    )
                ),
                array(
                    'collectApp' => 'collectApp2',
                    'section' => 'section2',
                    'data' => array('name2' => 'value2'),
                    'events' => array(
                        array(
                            'definitionId' => 'name2',
                            'data' => array('name2' => 'value2')
                        )
                    )
                )
            )
        ));
        $this->assertEquals('pid', $profile->getId());
        $attributes = $profile->getAttributes();
        $this->assertEquals(2, count($attributes));
        $attributes = $profile->getAttributes('collectApp1');
        $this->assertEquals(1, count($attributes));
        $attributes = $profile->getAttributes(null, 'section1');
        $this->assertEquals(1, count($attributes));
        $attribute = $profile->getAttribute('name1', 'collectApp1', 'section1');
        $this->assertEquals('value1', $attribute->getValue());
        $sessions = $profile->getSessions(function ($session) {
            return $session->getCollectApp() === 'collectApp1';
        });
        $this->assertEquals(1, count($sessions));
        $session = $profile->getSession('sid');
        $this->assertEquals('value1', $session->getDataValue('name1'));
    }    
    
    public function testShouldNotThrowErrorOnEmptyConfig () {
        $this->assertInstanceOf('Innometrics\Profile', $this->createProfile());
        $this->assertInstanceOf('Innometrics\Profile', $this->createProfile(array()));
    }
    
    public function testShouldBeInitedWithDefaultData () {
        $profile = $this->createProfile();
        $sessions = $profile->getSessions();
        $attributes = $profile->getAttributes();
        
        $profileId = $profile->getId();
        $this->assertTrue(is_string($profileId));
        $this->assertEquals(32, strlen($profileId));
        
        $this->assertTrue(is_array($sessions));
        $this->assertCount(0, $sessions);
        
        $this->assertTrue(is_array($attributes));
        $this->assertCount(0, $attributes);
    }
    
    public function testShouldUseIdFromConfig () {
        $profileId = 'pid';
        $profile = $this->createProfile(array(
            'id' => $profileId
        ));
        
        $this->assertEquals($profileId, $profile->getId());
    }
    
    public function testShouldProperlySerializeProfile () {
        $profileData = $this->profileData;
        $profile = $this->createProfile($profileData);
        
        $serializedData = $profileData;
        foreach ($serializedData['attributes'] as $key => $item) {
            $item['data'] = (object) $item['data'];
            $item = (object) $item;
            $serializedData['attributes'][$key] = $item;
        }
        foreach ($serializedData['sessions'] as $key => $item) {
            $item['data'] = (object) $item['data'];
            
            foreach ($item['events'] as $key2 => $item2) {
                $item2['data'] = (object) $item2['data'];
                $item2 = (object) $item2;
                $item['events'][$key2] = $item2;
            }
            
            $item = (object) $item;
            $serializedData['sessions'][$key] = $item;
        }
        
        $this->assertEquals((object) $serializedData, $profile->serialize());
    }
    
    public function testShouldSerializeOnlyChangedDataOfProfile () {
        $profileData = $this->profileData;
        $profile = $this->createProfile($profileData);
        
        $profile->resetDirty();
        $this->assertFalse($profile->hasChanges());
        
        $profile->getAttribute('test', 'app1', 'sec1')->setValue('babar');
        
        $profile->getSession('sid1')->addEvent(array(
            'id' => 'a',
            'definitionId' => 'b',
            'createdAt' => 1442476047268
        ));
        $profile->getSession('sid2')->setDataValue('dd', 'bb');

        $this->assertTrue($profile->hasChanges());
        
        $now = round(microtime(true) * 1000);
        
        $this->assertEquals((object) array(
            'id' => 'pid',
            'attributes' => array(
                (object) array(
                    'collectApp' => 'app1',
                    'section' => 'sec1',
                    'data' => (object) array(
                        'test' => 'babar'
                    )
                )
            ),
            'sessions' => array(
                (object) array(
                    'id' => 'sid1',
                    'collectApp' => 'app1',
                    'section' => 'sec1',
                    'createdAt' => 1442476047267,
                    'modifiedAt' => 1442476047267,
                    'data' => (object) array(),
                    'events' => array(
                        (object) array(
                            'id' => 'a',
                            'definitionId' => 'b',
                            'data' => (object) array(),
                            'createdAt' => 1442476047268
                        )
                    )
                ),
                (object) array(
                    'id' => 'sid2',
                    'collectApp' => 'app2',
                    'section' => 'sec2',
                    'createdAt' => 1442476047267,
                    'modifiedAt' => 1442476047267,
                    'data' => (object) array(
                        'dd' => 'bb'
                    ),
                    'events' => array()
                )
            ) 
        ), $profile->serialize(true));
    }    
    
    public function testShouldReturnErrorIfProfileIsNotInstanceOfProfileWhileProfileMerging () {
        $profile = $this->createProfile();
        
        foreach (array(null, true, array()) as $value) {
            try {
                $profile->merge($value);
            } catch (\Exception $e) {
                $this->assertStringStartsWith('Argument 1 passed to Innometrics\Profile::merge() must be an instance of Innometrics\Profile', $e->getMessage());
            }
        }
    }   
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Profile IDs should be similar
     */     
    public function testShouldReturnErrorIfIdsAreDifferentWhileProfileMerging () {
        $profile1 = $this->createProfile('pid1');
        $profile2 = $this->createProfile('pid2');
        
        $profile1->merge($profile2);
    }  
    
    public function testShouldProperlyMergeDataFromProfileToOtherOne () {
        $profile1 = $this->createProfile(array(
            "id" => 'pid',
            "attributes" => array(
                array(
                    "collectApp" => 'app1',
                    "section" => 'sec1',
                    "data" => array(
                        "foo" => 'bar',
                        "test" => 1
                    )
                )
            ),
            "sessions" => array(
                array(
                    "id" => 'sid1',
                    "collectApp" => 'app1',
                    "section" => 'sec1',
                    "data" => array(
                        "data1" => 'value1'
                    ),
                    "events" => array()
                ),
                array(
                    "id" => 'sid2',
                    "collectApp" => 'app2',
                    "section" => 'sec2',
                    "data" => array(
                        "test1" => 'q',
                        "test2" => 'w'
                    ),
                    "events" => array(
                        "id" => 'ev1',
                        "definitionId" => 'def1',
                        "createdAt" => 5,
                        "data" => array(
                            "spider" => 'man'
                        )
                    )
                )
            )
        ));
        
        $profile2 = $this->createProfile(array(
            "id" => 'pid',
            "attributes" => array(
                array(
                    "collectApp" => 'app1',
                    "section" => 'sec1',
                    "data" => array(
                        "foo" => 'baz'
                    )
                ),
                array(
                    "collectApp" => 'app2',
                    "section" => 'sec2',
                    "data" => array(
                        "cat" => 'dog'
                    )
                )
            ),
            "sessions" => array(
                array(
                    "id" => 'sid0',
                    "collectApp" => 'app1',
                    "section" => 'sec2',
                    "data" => array(
                        "car" => 'moto'
                    ),
                    "events" => array()
                ),
                array(
                    "id" => 'sid2',
                    "collectApp" => 'app2',
                    "section" => 'sec2',
                    "data" => array(
                        "test1" => 'e'
                    ),
                    "events" => array(
                        array(
                            "id" => 'ev1',
                            "definitionId" => 'def1',
                            "data" => array(
                                "spider" => 'fly',
                                "java" => 'script'
                            )
                        )
                    )
                )
            )
        ));
        
        $profile1->merge($profile2);
        
        $this->assertCount(3, $profile1->getAttributes());
        $this->assertEquals('baz', $profile1->getAttribute('foo', 'app1', 'sec1')->getValue());
        
        $this->assertCount(3, $profile1->getSessions());
        $this->assertEquals(array(
            'test1' => 'e',
            'test2' => 'w'
        ), $profile1->getSession('sid2')->getData());
    }
    
    public function testShouldBeChangedIfHasChangedAttribute () {
        $profile = $this->createProfile();
        $this->assertFalse($profile->hasChanges());
        
        $profile->setAttribute(array(
            'collectApp' => 'web',
            'section' => 'sec',
            'name' => 'a',
            'value' => 1
        ));
        $this->assertTrue($profile->hasChanges());
    }
    
    public function testShouldBeChangedIfHasChangedSession() {
        $profile = $this->createProfile();
        $this->assertFalse($profile->hasChanges());
        
        $profile->setSession(array(
            'collectApp' => 'web',
            'section' => 'sec',
            'id' => '1'
        ));
        $this->assertTrue($profile->hasChanges());
    }
    
    public function testShouldNotChangedAfterResetDirty() {
        $profile = $this->createProfile(array(
            'attributes' => array(
                array(
                    "collectApp" => 'web',
                    "section" => 'sec',
                    "data" => array(
                        "a" => 1
                    )
                )
            )
        ));
        $this->assertTrue($profile->hasChanges());
        
        $profile->resetDirty();
        $this->assertFalse($profile->hasChanges());
    }
    
}
