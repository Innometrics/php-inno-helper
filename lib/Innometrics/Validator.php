<?php

namespace Innometrics;

use JsonSchema;
use Json;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Validator {
    
    protected static $path = '/../../schema/';
    
    protected static function getSchemasPath () {
        return realpath(__DIR__ . self::$path);
    }
    
    protected static function getSchemaPath ($id) {
        return self::getSchemasPath() . '/' . $id . '.json';
    }
    
    protected static function isValid ($id, $data) {
        /*
        $validator = new Json\Validator(self::getSchemaPath($id));
        try {
            $validator->validate((object) $data);
        } catch (Json\ValidationException $ex) {
            echo var_dump($id, $ex->getMessage());
            die;
        }
        
        return true;
         */
        
        JsonSchema\RefResolver::$maxDepth = 10;
        
        $retriever = new JsonSchema\Uri\UriRetriever;
        
        $schema = $retriever->retrieve('file://' . self::getSchemaPath($id));

        $refResolver = new JsonSchema\RefResolver($retriever);
        $refResolver->resolve($schema, 'file://' . self::getSchemasPath() . '/');

        $validator = new JsonSchema\Validator();
        $validator->check($data, $schema);
        
        /*
        if (!$validator->isValid()) {
            echo var_dump($id, $data, $validator->getErrors());
            die;
        }
         */
        
        return $validator->isValid();
    }
    
    public static function isAttributeValid ($data) {
        return self::isValid('attribute', $data);
    }
    
    public static function isEventValid ($data) {
        return self::isValid('event', $data);
    }
    
    public static function isProfileValid ($data) {
        return self::isValid('profile', $data);
    }
    
    public static function isSessionValid ($data) {
        return self::isValid('session', $data);
    }
    
}
