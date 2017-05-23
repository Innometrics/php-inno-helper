<?php

namespace Helper;

require_once('vendor/autoload.php');

use Innometrics\Helper;

class Base extends \PHPUnit_Framework_TestCase {

    protected $helper = null;

    protected $config = array(
        'bucketName' => 'bucketName',
        'appName' => 'appName',
        'appKey' => 'appKey',
        'apiUrl' => 'apiUrl',
        'groupId' => 4,
        'schedulerApiHost' => 'schedulerApiHost'
    );

    protected function setUp() {}

    protected function tearDown() {
        if ($this->helper) {
            $this->helper->clearCache();
        }
    }

    protected function createHelper ($config = null) {
        $helper = new Helper($config ?: $this->config);
        $this->helper = $helper;
        return $helper;
    }
}
