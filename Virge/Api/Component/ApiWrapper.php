<?php
namespace Virge\Api\Component;

use Virge\Cli;
use Virge\Core\Config;

/**
 * Virge ApiWrapper
 * should be overwritten by custom ApiWrapper to do proper logging of debug messages
 */
class ApiWrapper
{
    protected static $API_VERSION = '1';
    protected static $API_USE_HTTPS = false;
    public static $debug = false;
    protected static $inited = false;
    protected static $url = '';
    protected static $result;
    protected static $info;
    protected static $lastMethodCalled = null;

    public static function setApiVersion($apiVersion)
    {
        self::$API_VERSION = $apiVersion;
    }

    public static function setApiUseHttps($apiUseHttps)
    {
        self::$API_USE_HTTPS = $apiUseHttps;
    }

    public static function getLastResult()
    {
        return self::$result;
    }

    public static function getLastInfo()
    {
        return self::$info;
    }
    
    protected static function init() {
        if(self::$inited) {
            return;
        }

        self::$url = Config::get('app', 'virge_api_domain');
        self::$inited = true;
    }

    protected static function _get(string $url, $params = [])
    {
        self::init();
        $params['_vApi'] = Config::get('app', 'internal_api_key');

        return self::_call('GET', self::buildUrl($url, $params));
    }
    
    protected static function _post(string $url, $postFields = [])
    {
        self::init();
        $postFields['_vApi'] = Config::get('app', 'internal_api_key');

        return self::_call('POST', self::buildUrl($url, $postFields, false), $postFields);
    }

    protected static function _delete(string $url, $params = [])
    {
        self::init();
        $params['_vApi'] = Config::get('app', 'internal_api_key');

        return self::_call('DELETE', self::buildUrl($url, $params));
    }

    protected static function _call($httpMethod, $url, $postFields = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        switch($httpMethod)
        {
            case 'POST':
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
                break;
        }

        self::$result = curl_exec($ch);
        self::$info = curl_getinfo($ch);
        curl_close($ch);

        self::$lastMethodCalled = $url;

        if(static::$debug) {
            static::logLastRequest();
        }

        if(!self::$info['http_code'] || self::$info['http_code'] < 200 || self::$info['http_code'] >= 300) {
            return false;
        }

        $response = json_decode(self::$result, true);
        
        if(!$response || !array_key_exists('data', $response)) {
            return false;
        }

        //should all be using our unified message wrapper, just return the data portion
        return $response['data'];
    }

    protected static function buildUrl($method, &$params = [], $addToQuery = true) : string
    {
        //replace variables inside of the method 
        foreach($params as $key => $value) {
            $count = 0;
            $method = str_replace(sprintf('{%s}', $key), $value, $method, $count);
            
            if($count) {
                unset($params[$key]);
            }
        }

        $scheme = self::$API_USE_HTTPS ? 'https' : 'http';
        if($addToQuery) {
            $queryStr = count($params) > 0 ? '?' . http_build_query($params) : '';
        } else {
            $queryStr = '';
        }


        return sprintf("%s://%s/api/v/%s/%s%s", $scheme, self::$url, self::$API_VERSION, $method, $queryStr);
    }

    protected static function logLastRequest()
    {
        $message = sprintf("%s\n%s::%s\nResult:\n%s\nInfo:\n%s\n\n", (new \DateTime)->format('Y-m-d H:i:s'), static::class, self::$lastMethodCalled, var_export(self::$result, true), var_export(self::$info, true));
        Cli::output($message);
        return;
    }

}