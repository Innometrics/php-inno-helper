<?php

namespace Innometrics;

use Innometrics\Profile;
use Innometrics\Segment;

/**
 * InnoHelper TODO add description
 * @copyright 2015 Innometrics
 */
class Helper {

    /**
     * Bucket name
     * @var string
     */
    protected $bucketName = null;

    /**
     * Application name
     * @var string
     */
    protected $appName = null;

    /**
     * Company id
     * @var mixed
     */
    protected $groupId = null;

    /**
     * Application key
     * @var string
     */
    protected $appKey = null;

    /**
     * API url
     * @var string
     */
    protected $apiUrl = null;    

    /**
     * Construct Helper instance
     *
     * <b>Example:</b>
     *      $helper = new Innometrics\Helper(array(
     *          "bucketName"    => "testbucket",
     *          "appKey"        => "df8JG35sKf",
     *          "appName"       => "myphpapp",
     *          "groupId"       =>  42,
     *          "apiUrl"        => "http://api.innomdc.com"
     *      ));
     *
     * @param array $config Initial environment variables
     */
    public function __construct($config = array()) {
        $this->validateConfig($config);
        $this->groupId = $config['groupId'];
        $this->apiUrl = $config['apiUrl'];
        $this->bucketName = $config['bucketName'];
        $this->appName = $config['appName'];
        $this->appKey = $config['appKey'];   
    }
    
    /**
     * Get application name
     * @return string
     */
    public function getCollectApp () {
        return $this->appName;
    }

    /**
     * Get bucket name
     * @return string
     */
    public function getBucket () {
        return $this->bucketName;
    }

    /**
     * Get company id
     * @return mixed
     */
    public function getCompany () {
        return $this->groupId;
    }

    /**
     * Get application key
     * @return string
     */
    public function getAppKey () {
        return $this->appKey;
    }

    /**
     * Get Api url
     * @return string
     */
    public function getApiHost () {
        return $this->apiUrl;
    }
    
    /**
     * Build Url for API request to work with certain Profile
     *
     * <b>Example:</b>
     *      $url = $helper->getProfileUrl("vze0bxh4qpso67t2dxfc7u81a5nxvefc");
     *      echo $url;
     *      ------->
     *      http://api.innomdc.com/v1/companies/42/buckets/testbucket/profiles/vze0bxh4qpso67t2dxfc7u81a5nxvefc
     *
     * @param string $profileId
     * @return string URL to make API request
     */    
    protected function getProfileUrl ($profileId) {
        return sprintf(
            '%s/v1/companies/%s/buckets/%s/profiles/%s?app_key=%s',
            $this->getApiHost(),
            $this->getCompany(),
            $this->getBucket(),
            $profileId,
            $this->getAppKey()
        );
    }

    /**
     * Build Url for API request to work with application settings
     *
     * <b>Example:</b>
     *      $url = $this->getAppSettingsUrl();
     *      echo $url;
     *      ------->
     *      http://api.innomdc.com/v1/companies/42/buckets/testbucket/apps/testapp/custom?app_key=8HJ3hnaxErdJJ62H
     *
     * @return string URL to make API request
     */    
    protected function getAppSettingsUrl () {
        return sprintf(
            '%s/v1/companies/%s/buckets/%s/apps/%s/custom?app_key=%s',
            $this->getApiHost(),
            $this->getCompany(),
            $this->getBucket(),
            $this->getCollectApp(),
            $this->getAppKey()
        );
    }

    /**
     * Build Url for API request to work with segments
     * @return string
     */
    protected function getSegmentsUrl () {
        return sprintf(
            '%s/v1/companies/%s/buckets/%s/segments?app_key=%s',
            $this->getApiHost(),
            $this->getCompany(),
            $this->getBucket(),
            $this->getAppKey()
        );
    }

    /**
     * Build Url for API request to work with segments
     * @param array $params
     * @return string
     */
    public function getSegmentEvaluationUrl ($params = array()) {
        return sprintf(
            '%s/v1/companies/%s/buckets/%s/segment-evaluation?app_key=%s&%s',
            $this->getApiHost(),
            $this->getCompany(),
            $this->getBucket(),
            $this->getAppKey(),
            http_build_query($params)
        );
    }
    
    /**
     * Internal method to make http requests, curl used
     * @param array $params List of parameters to configure request
     * * $params['url']     - string, required.
     * * $params['type']    - string. Defines type of request. Possible values: 'POST' or 'GET' ('GET' used by default)
     * * $params['body']    - string. Request body.
     * * $params['qs']      - array. Key=>value pairs used to create "query" part of URL
     * * $params['headers'] - array. Custom HTTP headers
     * @return string|bool string with response or false if request failed
     */
    protected static function request ($params) {
        $curl = curl_init();
        
        $type = strtolower(isset($params['type']) ? $params['type'] : 'get');
        switch ($type) {
            case 'post':
            case 'put':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
                if (!empty($params['body'])) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $params['body']);
                }
                break;

            case 'get':
            default:
                if (!empty($params['qs'])) {
                    $params['url'] .= '?' . http_build_query($params['qs']);
                }
                break;
        }
        
        if (!empty($params['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $params['headers']);
        }
        
        curl_setopt($curl, CURLOPT_URL, $params['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        
        if ($response === false) {
            $error = curl_error($curl) ? curl_error($curl) : 'Unknown error';
            throw new \ErrorException($error);
        } else {
            curl_close($curl);
            return $response;
        }        
    }
    
    /**
     * Get application settings
     *
     * <b>Example:</b>
     *      try {
     *          $settings = $helper->getSettings();
     *          var_dump($settings);
     *          ------->
     *          [
     *              "stringSetting" => "qwe",
     *              "numberSetting" => 1,
     *              "arraySetting"  => ["one","two"]
     *          ]
     *
     *      } catch (\ErrorException $e) {
     *          // application has not settings at all
     *      }
     *
     * @param object|array $params Custom environment vars for retrieve settings
     * @return array
     * @throws \ErrorException If settings are not found exception will be thrown
     */
    public function getAppSettings () {
        $url = $this->getAppSettingsUrl();
        $response = $this->request(array(
            'url' => $url,
            'headers' => array(
                'Content-Type: application/json',
                'Accept: application/json'
            )
        ));
        $body = json_decode($response, true);
        
        $this->checkErrors($body);

        if (!isset($body['custom'])) {
            throw new \ErrorException('Custom settings not found');
        }

        return $body['custom'];
    }

    /**
     * Update application settings
     *
     * <b>Example:</b>
     *      $helper->setSettings(array(
     *          "stringSetting" => "foo",
     *          "numberSetting" => 42,
     *          "arraySetting"  => ["bar","baz"]
     *      ));
     *
     * @param object|array $settings Settings as key=>value pairs
     * @return bool|string
     */
    public function setAppSettings ($settings) {
        if (!is_array($settings)) {
            throw new \ErrorException('Settings should be an array');
        }        
        
        $settings = (object)$settings;
        $url = $this->getAppSettingsUrl();

        $requestParams = array(
            'url'   => $url,
            'type'  => 'put',
            'headers' => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
            'body' => json_encode(new \ArrayObject($settings))
        );

        $response = $this->request($requestParams);
        $body = json_decode($response, true);
        
        $this->checkErrors($body);
    }
    
    /**
     * Get segments
     * @return Segment[]
     */
    public function getSegments () {
        $url = $this->getSegmentsUrl();
        $response = $this->request(array(
            'url' => $url,
            'headers' => array(
                'Content-Type: application/json',
                'Accept: application/json'
            )
        ));

        $body = json_decode($response, true);
        
        $this->checkErrors($body);

        if (is_array($body)) {
            foreach ($body as $sgmData) {
                if (isset($sgmData['segment']) && is_array($sgmData['segment'])) {
                    $segments[] = new Segment($sgmData['segment']);
                }
            }
        }
        
        return $segments;
    }

    /**
     * Evaluate profile by segment
     * @param Profile $profile
     * @param Segment $segment
     * @return bool
     */
    public function evaluateProfileBySegment ($profile, $segment) {
        if (!($segment instanceof Segment)) {
            throw new \ErrorException('Argument "segment" should be a Segment instance');
        }
        
        return $this->evaluateProfileBySegmentId($profile, $segment->getId());
    }

    /**
     * Evaluate profile by segment's id
     * @param Profile $profile
     * @param string $segmentId
     * @return bool
     */
    public function evaluateProfileBySegmentId ($profile, $segmentId) {
        return $this->_evaluateProfileByParams($profile, array(
            'segment_id' => $segmentId
        ));
    }

    /**
     * Evaluate profile by IQL expression
     * @param Profile $profile
     * @param string $iql
     * @return bool
     */
    public function evaluateProfileByIql ($profile, $iql) {
        return $this->_evaluateProfileByParams($profile, array(
            'iql' => $iql
        ));
    }

    /**
     *
     * @param string $profileId
     * @return Profile
     */
    public function loadProfile ($profileId) {
        if (gettype(trim($profileId)) !== 'string') {
            throw new \ErrorException('ProfileId should be a non-empty string');
        }
        
        $url = $this->getProfileUrl($profileId);
        $requestParams = array(
            'url'   => $url,
            'type'  => 'get',
            'headers' => array(
                'Content-Type: application/json',
                'Accept: application/json'
            )            
        );

        $response = $this->request($requestParams);
        $body = json_decode($response, true);
        
        $this->checkErrors($body);
        
        $profile = new Profile($body['profile']);
        return $profile;
    }

    /**
     * Make Api request to delete profile
     * @param {String} profileId
     * @param {Function} callback
     */
    public function deleteProfile ($profileId) {
//        var self = this;
//        var opts = {
//            url: this.getProfileUrl(profileId),
//            json: true
//        };
//
//        request.del(opts, function (error, response) {
//            error = self.checkErrors(error, response, 204);
//
//            if (typeof callback === 'function') {
//                callback(error);
//            }
//        });
    }

    /**
     * Make Api request to save profile in DH
     * @param {Profile} profile
     * @param {Function} callback
     */
    public function saveProfile ($profile) {
//        var self = this;
//        var error = null;
//        var result = null;
//        
//        if (!(profile instanceof Profile)) {
//            error = new Error('Argument "profile" should be a Profile instance');
//            if (typeof callback === 'function') {
//                callback(error, result);
//            }
//            return;
//        }
//        
//        var profileId = profile.getId();
//        var opts = {
//            url: this.getProfileUrl(profileId),
//            body: profile.serialize(),
//            json: true
//        };
//
//        request.post(opts, function (error, response) {
//            var data;
//            error = self.checkErrors(error, response, [200, 201]);
//
//            if (!error) {
//                data = response.body;
//                if (data.hasOwnProperty('profile') && typeof data.profile === 'object') {
//                    try {
//                        profile = new Profile(data.profile);
//                    } catch (e) {
//                        error = e;
//                    }
//                }
//            }
//
//            if (typeof callback === 'function') {
//                callback(error, profile);
//            }
//        });
    }

    /**
     * Make Api request to merge two profiles
     * @param {Profile} profile1
     * @param {Profile} profile2
     * @param {Function} callback
     */
    public function mergeProfiles ($profile1, $profile2) {
//        var self = this;
//        var error = null;
//        var result = null;
//        
//        if (!(profile1 instanceof Profile)) {
//            error = new Error('Argument "profile1" should be a Profile instance');
//        } else if (!(profile2 instanceof Profile)) {
//            error = new Error('Argument "profile2" should be a Profile instance');
//        }
//        
//        if (error) {
//            if (typeof callback === 'function') {
//                callback(error, result);
//            }
//            return;
//        }
//        
//        var profileId = profile1.getId();
//        var opts = {
//            url: this.getProfileUrl(profileId),
//            body: {
//                id: profileId,
//                mergedProfiles: [
//                    profile2.getId()
//                ]
//            },
//            json: true
//        };
//        
//        request.post(opts, function (error, response) {
//            var data;
//            var profile = null;
//
//            error = self.checkErrors(error, response, [200, 201]);
//
//            if (!error) {
//                data = response.body;
//                if (data.hasOwnProperty('profile') && typeof data.profile === 'object') {
//                    try {
//                        profile = new Profile(data.profile);
//                    } catch (e) {
//                        error = e;
//                    }
//                }
//            }
//
//            if (typeof callback === 'function') {
//                callback(error, profile);
//            }
//
//        });
    }

    /**
     * Refresh  local profile with data from DH
     * @param {Profile} profile
     * @param {Function} callback
     */
    public function refreshLocalProfile ($profile) {
//        var error = null;
//        var result = null;
//        
//        if (!(profile instanceof Profile)) {
//            error = new Error('Argument "profile" should be a Profile instance');
//            if (typeof callback === 'function') {
//                callback(error, result);
//            }
//            return;
//        }
//        
//        var profileId = profile.getId();
//
//        this.loadProfile(profileId, function (error, loadedProfile) {
//            if (!error) {
//                profile.merge(loadedProfile);
//            }
//            
//            if (typeof callback === 'function') {
//                callback(error, profile);
//            }
//        });
    }

    /**
     * Try to parse profile data from request made by DH
     * @param {String} requestBody
     * @returns {Profile}
     */
    public function getProfileFromRequest ($requestBody) {
//        try {
//            if (typeof requestBody !== 'object') {
//                requestBody = JSON.parse(requestBody);
//            }
//        } catch (e) {
//            throw new Error('Wrong stream data');
//        }
//        var profile = requestBody.profile;
//        if (!profile) {
//            throw new Error('Profile not found');
//        }
//        return new Profile(profile);
    }

    /**
     *
     * @param {String} requestBody
     * @returns {Object}
     */
    public function getMetaFromRequest ($requestBody) {
//        try {
//            if (typeof requestBody !== 'object') {
//                requestBody = JSON.parse(requestBody);
//            }
//        } catch (e) {
//            throw new Error('Wrong stream data');
//        }
//        var meta = requestBody.meta;
//        if (!meta) {
//            throw new Error('Meta not found');
//        }
//        return meta;
    }

    /**
     * Create empty local profile with certain id
     * @param {String} profileId
     * @returns {Profile}
     */
    public function createProfile ($profileId) {
//        return new Profile({
//            id: profileId,
//            version: '1.0',
//            sessions: [],
//            attributes: [],
//            mergedProfiles: []
//        });
    }

    /**
     * Check that certain object has all fields from list
     * @param {Object} obj
     * @param {Array} fields
     * @returns {Error|null}
     * @private
     */
    public function validateObject ($obj, $fields) {
//        var error = null;
//        if (typeof obj !== 'object') {
//            error = new Error('Object is not defined');
//        } else {
//            try {
//                fields = Array.isArray(fields) ? fields : [fields];
//                fields.forEach(function (key) {
//                    if (!(key in obj)) {
//                        throw new Error(key.toUpperCase() + ' not found');
//                    }
//                });
//            } catch (e) {
//                error = e;
//            }
//        }
//        return error;
    }

    /**
     * Check for error and that response has allowed statusCode and required field(s)
     * @param array $response
     * @param integer|array $successCode
     */
    protected function checkErrors ($response, $successCode = 200) {
        $successCode = (array)$successCode;
        
        if (!$response) {
            throw new \ErrorException('Empty response');
        }
        
        if (isset($response['statusCode']) && !in_array($response['statusCode'], $successCode)) {
            throw new \ErrorException(sprintf('Server failed with status code %s: "%s"', $response['statusCode'], $response['message']));
        }        
    }

    /**
     *
     * @param Profile $profile
     * @param array $params
     */
    protected function _evaluateProfileByParams ($profile, $params) {
        if (!($profile instanceof Profile)) {
            throw new \ErrorException('Argument "profile" should be a Profile instance');
        }

        $defParams = array(
            'profile_id' => $profile->getId()
        );

        $params = array_merge($params, $defParams);
        
        $url = $this->getSegmentEvaluationUrl($params);
        
        $response = $this->request(array(
            'url' => $url,
            'headers' => array(
                'Content-Type: application/json',
                'Accept: application/json'
            )
        ));
        $body = json_decode($response, true);
        
        $this->checkErrors($body);  
        
        if (!(isset($body['segmentEvaluation']) && isset($body['segmentEvaluation']['result']))) {
            throw new \ErrorExcepion('Wrong evaluation response: ' . $body);
        }
        
        return $body['segmentEvaluation']['result'];
    }    
    
    
    


    
    
    
    
    
    
    
    
    /**
     * Parse start session data and set found environment variables
     *
     * <b>Example:</b>
     *      ........
     *      $content = $response->getContent();
     *      try {
     *          $data = $helper->getStreamData($content);
     *          var_dump($data);
     *          ------->
     *          stdClass Object
     *              (
     *                  [profile]   => stdClass Object,
     *                  [session]   => stdClass Object,
     *                  [event]     => stdClass Object,
     *                  [data]      => stdClass Object
     *              )
     *
     *      } catch (\ErrorException $e) {
     *          // content has not profile data
     *      }
     *
     * @param string $content
     * @return object Object with properties: profile, session, events, data
     */
    public function getStreamData($content) {
        $data = $this->parseStreamData($content);

        $this->setVar('profileId', $data['profile']['id']);
        $this->setVar('collectApp', $data['session']['collectApp']);
        $this->setVar('section', $data['session']['section']);

        return $data;
    }

    /**
     * Extract stream data from raw content.
     * Tries to find profile and its related parts
     *
     * <b>Example:</b>
     *      ........
     *      $content = $response->getContent();
     *      try {
     *          $data = $helper->parseStreamData($content);
     *          var_dump($data);
     *          ------->
     *          Array
     *              (
     *                  [profile]   => Array,
     *                  [session]   => Array,
     *                  [event]     => Array,
     *                  [data]      => Array
     *              )
     *
     *      } catch (\ErrorException $e) {
     *          // content has not profile data
     *      }
     *
     * @param mixed $rawData Data to parse
     * @return object Object with properties: profile, session, events, data
     * @throws \ErrorException If profile or some its required parts are not found exception will be thrown
     */
    public function parseStreamData ($rawData) {
        $data = $rawData;
        if (!is_object($data)) {
            $data = json_decode($data, true);
        }

        if (!isset($data['profile'])) {
            throw new \ErrorException('Profile not found');
        }
        $profile = $data['profile'];

        if(!isset($profile['id'])) {
            throw new \ErrorException('Profile id not found');
        }

        if(!isset($profile['sessions'][0])) {
            throw new \ErrorException('Session not found');
        }
        $session = $profile['sessions'][0];

        if(!isset($session['collectApp'])) {
            throw new \ErrorException('CollectApp not found');
        }

        if(!isset($session['section'])) {
            throw new \ErrorException('Section not found');
        }

        if(!isset($session['events'][0]['data'])) {
            throw new \ErrorException('Data not set');
        }

        $result = array(
            'profile'   => $profile,
            'session'   => $session,
            'event'     => $session['events'][0],
            'data'      => $session['events'][0]['data']
        );

        return $result;
    }


    /**
     * Get attributes of the profile
     *
     * <b>Example:</b>
     *      [{
     *          "collectApp" => "web",
     *          "section"    => "sec1",
     *          "data"       => [
     *              "attr1" => 1,
     *              "attr2" => 'hello'
     *          ]
     *      }, {
     *          "collectApp" => "myapp",
     *          "section"    => "mysec",
     *          "data"       => [
     *              "foo"   => "bar",
     *              "hello" => "world"
     *          ]
     *      }]
     *
     * @param object|array $params Custom environment vars for retrieve attributes
     * @return array Profile attributes
     * @throws \ErrorException If profile not found in request response exception will be thrown
     */
    public function getAttributes($params = null) {
        $params = (object)$params;
        $vars = $this->mergeVars($this->getVars(), $params);

        $url = $this->profileAppUrl(array(
            'groupId'       => $vars->groupId,
            'bucketName'    => $vars->bucketName,
            'appKey'        => $vars->appKey,
            'profileId'     => $vars->profileId
        ));

        $response = $this->request(array('url' => $url));

        $body = json_decode($response, true);

        if(!isset($body['profile'])) {
            throw new \ErrorException('Profile not found');
        }
        $attributes = array();
        if (!empty($body['profile']['attributes'])) {
            $attributes = $body['profile']['attributes'];
        }
        return $attributes;
    }

    /**
     * Update attributes of the profile
     * @param object|array $attributes Key=>value pairs with attributes
     * @param object|array $params Custom environment vars for update attributes
     * @return bool|string String with response or false if request failed
     */
    public function setAttributes($attributes, $params = null) {
        $params = (object)$params;
        $vars = $this->mergeVars($this->getVars(), $params);

        $url = $this->profileAppUrl(array(
            'groupId'       => $vars->groupId,
            'bucketName'    => $vars->bucketName,
            'appKey'        => $vars->appKey,
            'profileId'     => $vars->profileId
        ));

        $requestParams = array(
            'url'   => $url,
            'type'  => 'post',
            'headers' => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
            'body' => json_encode(array(
                'id' => $vars->profileId,
                'attributes' => array(array(
                    'collectApp'    => $vars->collectApp,
                    'section'       => $vars->section,
                    'data'          => new \ArrayObject($attributes)
                ))
            ))
        );

        return $this->request($requestParams);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    /**
     * Checks if config is valid
     * @throws \ErrorException If config are not suitable exception will be thrown
     */
    protected function validateConfig ($config = array()) {
        if (!is_array($config)) {
            throw new \ErrorException('Config should be an array');
        }

        $fields = array('bucketName', 'appName', 'appKey', 'apiUrl');
        foreach ($fields as $field) {
            if (!array_key_exists($field, $config)) {
                throw new \ErrorException('Property "' . field . '" in config should be defined');
            }
            if (gettype($config[$field]) !== 'string') {
                throw new \ErrorException('Property "' . field . '" in config should be a string');
            }
            if (!trim($config[$field])) {
                throw new \ErrorException('Property "' . field . '" in config can not be empty');
            }
        }

        if (!array_key_exists('groupId', $config)) {
            throw new \ErrorException('Property "groupId" in config should be defined');
        }
        
        $groupId = $config['groupId'];
        $groupIdType = gettype($groupId);
        if ($groupIdType !== 'string' && $groupIdType !== 'integer') {
            throw new \ErrorException('Property "groupId" in config should be a string or a number');
        }
        if (!trim((string)$groupId)) {
            throw new \ErrorException('Property "groupId" in config can not be empty');
        }
    } 
}
