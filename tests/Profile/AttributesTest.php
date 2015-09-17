<?php

namespace Profile;

require_once('vendor/autoload.php');
require_once 'Base.php';

class AttributesTest extends Base {
    
    public function testShouldCreateAttributesFromConfig () {
        $profile = $this->createProfile(array(
            'id' => 'pid',
            'attributes' => array(
                array(
                    'collectApp' => 'app',
                    'section' => 'sec',
                    'data' => array(
                        'test' => 1,
                        'foo' => 'bar'
                    )
                )
            )
        ));
        
        $attributes = $profile->getAttributes();
        
        $this->assertCount(2, $attributes);
        
        $attribute = $attributes[0];
        $this->assertEquals($attribute->getCollectApp(), 'app');
        $this->assertEquals($attribute->getSection(), 'sec');
        $this->assertEquals($attribute->getName(), 'test');
        $this->assertEquals($attribute->getValue(), 1);
        
        $attribute = $attributes[1];
        $this->assertEquals($attribute->getCollectApp(), 'app');
        $this->assertEquals($attribute->getSection(), 'sec');
        $this->assertEquals($attribute->getName(), 'foo');
        $this->assertEquals($attribute->getValue(), 'bar');
    }
    
}
