<?php

namespace Profile;

require_once('vendor/autoload.php');
require_once 'Base.php';

use Innometrics\Attribute;

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
    
    public function testShouldNotCreateAttributeFromConfigIfItHasNoData () {
        $profile = $this->createProfile(array(
            'id' => 'pid',
            'attributes' => array(
                array(
                    'collectApp' => 'app',
                    'section' => 'sec',
                    'data' => array()
                )
            )
        ));
        
        $attributes = $profile->getAttributes();
        
        $this->assertCount(0, $attributes);        
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage collectApp and section should be filled to create attribute correctly
     */     
    public function testShouldThrowErrorIfCollectAppIsEmpty () {
        $profile = $this->createProfile();
        $profile->createAttributes(null, 'section', array());
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage collectApp and section should be filled to create attribute correctly
     */     
    public function testShouldThrowErrorIfSectionIsEmpty () {
        $profile = $this->createProfile();
        $profile->createAttributes('app', null, array());
    }
    
    public function testShouldThrowErrorOnWrongData () {
        $profile = $this->createProfile();
        foreach (array(null, true, array()) as $value) {
            try {
                $profile->createAttributes('app', 'section', $value);
            } catch (\ErrorException $ex) {
                $this->assertEquals($ex->getMessage(), 'attributes should be an array');
            }
        }
    }
    
    public function testShouldCreateAttributes () {
        $profile = $this->createProfile();
        $attributes = $profile->createAttributes('app', 'sec', array(
            'foo' => 1,
            'bar' => 2
        ));
        $this->assertCount(2, $attributes);
        
        foreach ($attributes as $attr) {
            $this->assertInstanceOf('Innometrics\Attribute', $attr);
        }
    }
    
    public function testShouldThrowErrorOnWrongDataWhileSettingAttrs () {
        $profile = $this->createProfile();
        
        foreach (array(null, true, array()) as $value) {
            try {
                $profile->setAttributes($value);
            } catch (\ErrorException $ex) {
                $this->assertEquals($ex->getMessage(), 'Argument "attributes" should be an array');
            }
        }
    }
    
    /**
     * @expectedException        ErrorException
     * @expectedExceptionMessage Attribute is not valid
     */
    public function testShouldThrowErrorOnInvalidAttrWhileSettingAttrs () {
        $profile = $this->createProfile();
        $profile->setAttributes(array(
            'collectApp' => 'app',
            'section' => 'sec',
            'no' => 'name!',
            'value' => 'hi'
        ));
    }
    
    public function testShouldSetAttributes () {
        $profile = $this->createProfile();
        $attribute1 = array(
            'collectApp' => 'app',
            'section' => 'sec',
            'name' => 'test',
            'value' => 'hi'
        );
        $attribute2 = new Attribute(array(
            'collectApp' => 'app',
            'section' => 'sec',
            'name' => 'foo',
            'value' => 'bar'
        ));
        $profile->setAttributes(array(
            $attribute1,
            $attribute2
        ));
        $this->assertCount(2, $profile->getAttributes());
        
        $attribute = $profile->getAttribute($attribute1['name'], $attribute1['collectApp'], $attribute1['section']);
        $this->assertEquals($attribute->getValue(), $attribute1['value']);
        
        $attribute = $profile->getAttribute($attribute2->getName(), $attribute2->getCollectApp(), $attribute2->getSection());
        $this->assertEquals($attribute->getValue(), $attribute2->getValue());
    }
    
    public function testShouldSetAttributeAndRewriteExistingValue () {
        $profile = $this->createProfile();
        $attribute1 = array(
            'collectApp' => 'app',
            'section' => 'sec',
            'name' => 'test',
            'value' => 'hi'
        );
        $attribute2 = array(
            'collectApp' => 'app',
            'section' => 'sec',
            'name' => 'test',
            'value' => 'hi again'
        );
        $profile->setAttributes(array(
            $attribute1,
            $attribute2
        ));
        
        $this->assertCount(1, $profile->getAttributes());
        
        $attribute = $profile->getAttribute($attribute1['name'], $attribute1['collectApp'], $attribute1['section']);
        $this->assertEquals($attribute->getValue(), $attribute2['value']);
    }
    
    public function testShouldDelegateSetAttributeToSetAttributes () {
        $profile = $this->createProfile();
        $attribute = array(
            'collectApp' => 'app',
            'section' => 'sec',
            'name' => 'test',
            'value' => 'hi'
        );
        
        $profile = $this->getMock('Innometrics\Profile', array('setAttributes'));
        $profile
            ->expects($this->once())
            ->method('setAttributes')
            ->with($this->equalTo(array($attribute)));            

        $profile->setAttribute($attribute);
    }
    
    public function testShouldThrowErrorOnWrongArgsGettingAttr () {
        $profile = $this->createProfile();
        
        $argsArr = array(
            array(null, 'app', 'section'),
            array('name', null, 'section'),
            array('name', 'app', null)
        );
        
        foreach ($argsArr as $args) {
            try {
                call_user_func_array(array($profile, 'getAttribute'), $args);
            } catch (\ErrorException $ex) {
                $this->assertEquals($ex->getMessage(), 'Name, collectApp and section should be filled to get attribute');
            }
        }        
    }
    
    public function testShouldReturnNullIfAttributeDoesNotExist () {
        $profile = $this->createProfile();
        $this->assertNull($profile->getAttribute('name', 'app', 'sec'));
    }
    
    public function testShouldReturnAttribute () {
        $profile = $this->createProfile();
        $attributeData = array(
            'collectApp' => 'app',
            'section' => 'sec',
            'name' => 'test',
            'value' => 'hi'
        );
        $profile->setAttribute($attributeData);
        $attribute = $profile->getAttribute($attributeData['name'], $attributeData['collectApp'], $attributeData['section']);
        
        $this->assertInstanceOf('Innometrics\Attribute', $attribute);
        $this->assertEquals($attribute->getValue(), $attributeData['value']);
    }
}
