<?php

namespace Innometrics;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class IdGenerator {
    
    protected static $_instance;
    private function __construct(){}
    private function __clone(){}
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public static function generate ($length = 32) {
        if (gettype($length) !== 'integer') {
            throw new \ErrorException('Length should be a number');
        }
        if ($length < 0) {
            throw new \ErrorException('Length should be positive');
        }

        $id = '';
        
        $hashPart = self::getHashPart(self::getEnvStr(self::getEnvObj()));
        $randPart = self::getRandPart($length - strlen($hashPart));

        // depending on the length of the hash, which is variable, we place it at the beginning or the end of the ID
        if (count($hashPart) % 2) {
            $id = $hashPart . $randPart;
        } else {
            $id = $randPart . $hashPart;
        }

        return substr(sha1($id), 0, $length);
    }

    /**
     *
     * @return integer
     */
    protected static function rnd ($from = 1, $to = 9) {
        return mt_rand($from, $to);
    }

    /**
     * Create an object from environment values
     * @return array Contains values as string from environment values or random number if absent
     */
    protected static function getEnvObj () {
        return array(
            'vr' => (string) phpversion() . self::rnd(),
            'ah' => (string) php_uname('m') . self::rnd(),
            'pl' => (string) php_uname('s') . self::rnd()
        );
    }

    /**
     * Get an object containing only values and return a String
     * @param  array $envObj Object containing string representing browser environment values
     * @return string String to be hashed
     */
    protected static function getEnvStr ($envObj) {
        return implode('', $envObj);
    }

    /**
     * create a 32 bit hash from a String
     * @param  string $envStr String composed from environment variables
     * @return string        String composed of [0-9a-z]
     */
    protected static function getHashPart ($envStr) {
        $hash = '0';
        $envLgt = strlen($envStr);

        for ($i = 0; $i < $envLgt; $i += 1) {
            $hash .= ord($envStr[$i]);
        }
        
        return (string) base_convert(abs($hash), 10, 36);
    }

    /**
     * generate the random part of the hash thanks to hash already created and idLgt
     * @param  string $hashPart hash of environment variables
     * @return string Random string composed of [0-9a-z]
     */
    /**
     *
     * @param integer $length
     * @returns string
     */
    protected static function getRandPart ($length) {
        $randPart = "";
        for ($i = 0; $i < $length; $i++) {
            $randPart .= self::rnd(1, time()) ;
        }
        
        return base_convert($randPart, 10, 36);
    }

}
