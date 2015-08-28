<?php

require_once __DIR__ . '/../lib/Innometrics/Attribute.php';

use Innometrics\Attribute;

class AttributeTest extends PHPUnit_Framework_TestCase {

    protected function createAttribute ($config = array()) {
        return new Attribute($config);
    }

    public function testShouldNotThrowErrorOnIsValid () {
        $attribute = $this->createAttribute(array(
            'collectApp' => 'collectApp1',
            'section'    => 'section1',
            'name'       => 'name1',
            'value'      => 'value1'
        ));
        $this->assertTrue($attribute->isValid());
    }

    public function testShouldReceiveProperties () {
        $attribute = $this->createAttribute(array(
            'collectApp' => 'collectApp1',
            'section'    => 'section1',
            'name'       => 'name1',
            'value'      => 'value1'
        ));

        $attribute->setCollectApp('collectApp2');
        $attribute->setSection('section2');
        $attribute->setName('name2');
        $attribute->setValue('value2');

        $this->assertEquals('collectApp2', $attribute->getCollectApp(), 'setCollectApp test');
        $this->assertEquals('section2', $attribute->getSection(), 'setSection test');
        $this->assertEquals('name2', $attribute->getName(), 'setName test');
        $this->assertEquals('value2', $attribute->getValue(), 'setValue test');
    }

}
