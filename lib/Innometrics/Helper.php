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
     * @var string|integer
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
     * @return string|integer
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
     * @throws \ErrorException If request was failed due to internal problems
     */
    protected static function request ($params) {
        $curl = curl_init();
        
        $type = strtolower(isset($params['type']) ? $params['type'] : 'get');
        switch ($type) {
            case 'post':
            case 'put':
            case 'delete':
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
        
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json'
        );
        if (isset($params['headers']) && !empty($params['headers'])) {
            $headers = array_merge($headers, $params['headers']);
            $headers = array_unique($headers);
        }
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $params['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        
        if ($response === false) {
            $error = curl_error($curl) ? curl_error($curl) : 'Unknown error';
            throw new \ErrorException($error);
        } else {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            curl_close($curl);
            
            return array(
                'body'      => json_decode($response, true),
                'httpCode'  => $httpCode
            );
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
            'url' => $url
        ));
        
        $this->checkErrors($response);

        $body = $response['body'];
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
        
        $url = $this->getAppSettingsUrl();
        $response = $this->request(array(
            'url'  => $url,
            'type' => 'put',
            'body' => json_encode($settings)
        ));
        
        $this->checkErrors($response);
    }
    
    /**
     * Get segments
     * @return Segment[]
     */
    public function getSegments () {
        $url = $this->getSegmentsUrl();
        $response = $this->request(array(
            'url' => $url
        ));

        $this->checkErrors($response);

        $body = $response['body'];        
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
    public function evaluateProfileBySegment (Profile $profile, Segment $segment) {
        return $this->evaluateProfileBySegmentId($profile, $segment->getId());
    }

    /**
     * Evaluate profile by segment's id
     * @param Profile $profile
     * @param string $segmentId
     * @return bool
     */
    public function evaluateProfileBySegmentId (Profile $profile, $segmentId) {
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
     * Make Api request to load profile
     * @param string $profileId
     * @return Profile
     */
    public function loadProfile ($profileId) {
        $profileId = trim($profileId);
        $profile = null;
        
        if (empty($profileId) || gettype($profileId) !== 'string') {
            throw new \ErrorException('ProfileId should be a non-empty string');
        }
        
        $url = $this->getProfileUrl($profileId);
        $response = $this->request(array(
            'url' => $url         
        ));
        $this->checkErrors($response); 
        
        $body = $response['body'];
        
        if (isset($body['profile']) && is_array($body['profile'])) {
            $profile = new Profile($body['profile']);
        }
        
        return $profile;
    }

    /**
     * Make Api request to delete profile
     * @param string $profileId
     * @return bool
     */
    public function deleteProfile ($profileId) {
        $profileId = trim($profileId);
        if (empty($profileId) || gettype($profileId) !== 'string') {
            throw new \ErrorException('ProfileId should be a non-empty string');
        }
        
        $url = $this->getProfileUrl($profileId);
        $response = $this->request(array(
            'url'   => $url,
            'type'  => 'delete'     
        ));
        
        $this->checkErrors($response, 204);
        
        return true;
    }

    /**
     * Make Api request to save profile
     * @param Profile $profile
     * @return Profile
     */
    public function saveProfile (Profile $profile) {
        $profileId = $profile->getId();
        $url = $this->getProfileUrl($profileId);
        $response = $this->request(array(
            'url'  => $url,
            'type' => 'post',
            'body' => json_encode($profile->serialize())
        ));
        
        $this->checkErrors($response, array(200, 201));     
        
        $body = $response['body'];
        if (isset($body['profile']) && is_array($body['profile'])) {
            $profile = new Profile($body['profile']);
        }
        
        return $profile;
    }

    /**
     * Make Api request to merge two profiles
     * @param Profile $profile1
     * @param Profile $profile2
     * @return Profile
     */
    public function mergeProfiles (Profile $profile1, Profile $profile2) {
        $profileId = $profile1->getId();
        $url = $this->getProfileUrl($profileId);
        $response = $this->request(array(
            'url'   => $url,
            'type'  => 'post',
            'body' => json_encode(array(
                'id' => $profileId,
                'mergedProfiles' => array(
                    $profile2->getId()
                )
            ))
        ));
        
        $this->checkErrors($response, array(200, 201));     
        
        $profile = null;
        $body = $response['body'];
        if (isset($body['profile']) && is_array($body['profile'])) {
            $profile = new Profile($body['profile']);
        }
        
        return $profile;        
    }

    /**
     * Refresh local profile with data from DH
     * @param Profile $profile
     * @return Profile
     */
    public function refreshLocalProfile (Profile $profile) {
        $profileId = $profile->getId();
        $loadedProfile = $this->loadProfile($profileId);
        $profile->merge($loadedProfile);
        
        return $profile;
    }

    /**
     * Try to parse profile data from request made by DH
     * @param string|array $requestBody
     * @return Profile
     */
    public function getProfileFromRequest ($requestBody) {
        if (!is_array($requestBody)) {
            $requestBody = json_decode($requestBody, true);
        }
        
        if (!isset($requestBody['profile'])) {
            throw new \ErrorException('Profile not found');
        }
        
        return new Profile($requestBody['profile']);
    }

    /**
     * Try to parse meta data from request made by DH
     * @param string|array $requestBody
     * @return array
     */
    public function getMetaFromRequest ($requestBody) {
        if (!is_array($requestBody)) {
            $requestBody = json_decode($requestBody, true);
        }
        
        if (!isset($requestBody['meta'])) {
            throw new \ErrorException('Meta not found');
        }        
        
        return $requestBody['meta'];        
    }

    /**
     * Create empty local profile with certain id
     * @param string $profileId
     * @return Profile
     */
    public function createProfile ($profileId = null) {
        return new Profile(array(
            'id' => $profileId,
            'version' => '1.0',
            'sessions' => array(),
            'attributes' => array(),
            'mergedProfiles' => array()
        ));
    }

    /**
     * Check for error and that response has allowed statusCode and required field(s)
     * @param array $response
     * @param integer|array $successCode
     * @throws \ErrorException On empty response and non-successful response codes
     */
    protected function checkErrors ($response, $successCode = 200) {
        $successCode = (array)$successCode;
        $body = $response['body'];
        $httpCode = $response['httpCode'];
        
        if (!$response) {
            throw new \ErrorException('Empty response');
        }
        
        if (
            !in_array($httpCode, $successCode) ||
            isset($body['statusCode']) && !in_array($body['statusCode'], $successCode)
        ) {
            if (isset($body['statusCode'])) {
                $msg = sprintf('Server failed with status code %s: "%s"', $body['statusCode'], $body['message']);
            } else {
                $msg = sprintf('Server failed with status code %s', $httpCode);
            }
            
            throw new \ErrorException($msg);
        }        
    }

    /**
     *
     * @param Profile $profile
     * @param array $params
     * @return bool
     */
    protected function _evaluateProfileByParams (Profile $profile, $params) {
        $defParams = array(
            'profile_id' => $profile->getId()
        );

        $params = array_merge($params, $defParams);
        
        $url = $this->getSegmentEvaluationUrl($params);
        
        $response = $this->request(array(
            'url' => $url
        ));
        
        $this->checkErrors($response);  
        
        $body = $response['body'];
        if (!(isset($body['segmentEvaluation']) && isset($body['segmentEvaluation']['result']))) {
            throw new \ErrorExcepion('Wrong evaluation response: ' . $body);
        }
        
        return $body['segmentEvaluation']['result'];
    }    
    
    /**
     * Checks if config is valid
     * @throws \ErrorException If config are not suitable exception will be thrown
     */
    protected function validateConfig ($config = array()) {
        if (!is_array($config) || !count($config)) {
            throw new \ErrorException('Config should be a non-empty array');
        }

        $fields = array('bucketName', 'appName', 'appKey', 'apiUrl');
        foreach ($fields as $field) {
            if (!array_key_exists($field, $config)) {
                throw new \ErrorException('Property "' . $field . '" in config should be defined');
            }
            if (gettype($config[$field]) !== 'string') {
                throw new \ErrorException('Property "' . $field . '" in config should be a string');
            }
            if (!trim($config[$field])) {
                throw new \ErrorException('Property "' . $field . '" in config can not be empty');
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
