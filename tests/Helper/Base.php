<?php

namespace Helper;

require_once('vendor/autoload.php');

use Innometrics\Helper;

class Base extends \PHPUnit_Framework_TestCase {
    
    protected $config = array(
        'bucketName' => 'bucketName',
        'appName' => 'appName',
        'appKey' => 'appKey',
        'apiUrl' => 'apiUrl',
        'groupId' => 4
    );
    
    protected function createHelper ($config = array()) {
        return new Helper($config);
    }
}
