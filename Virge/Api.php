<?php
namespace Virge;

use Virge\Api\Component\Method;
use Virge\Api\Exception\ApiException;
use Virge\Router\Component\Request;

/**
 * 
 * @author Michael Kramer
 */
class Api 
{
    const VALID_URI_REGEX = "/\{[a-z\_\-\+\.]+\}/i";

    protected static $methods = array();
    protected static $versions = array();
    protected static $errors = array();
    protected static $last_error = '';
    protected static $verify = array();

    /**
     * @param string $method_name
     * @return Method
     */
    public static function get($method_name) {
        return self::method('get', $method_name);
    }
    
    /**
     * @param string $method_name
     * @return Method
     */
    public static function post($method_name) {
        return self::method('post', $method_name);
    }
    
    /**
     * @param string $method_name
     * @return Method
     */
    public static function put($method_name) {
        return self::method('put', $method_name);
    }
    
    /**
     * @param string $method_name
     * @return Method
     */
    public static function delete($method_name) {
        return self::method('delete', $method_name);
    }
    
    /**
     * Make sure a version of the API exists and is active
     * @param string $version
     * @param string $apiMethod
     * @param Request $request
     */
    public static function check($version, $apiMethod, Request $request) {
        
        if (in_array($version, self::$versions)) {
            
            $requestMethod = strtolower($request->getServer()->get('REQUEST_METHOD'));

            $apiMethod = self::getNormalizedRoute($requestMethod, $apiMethod, $request);
            
            //see if this api method exists for this specific version or for all
            if (isset(self::$methods[$requestMethod]) && isset(self::$methods[$requestMethod][$apiMethod]) && self::$methods[$requestMethod][$apiMethod]->canCall($version)) {
                return true;
            }
            
            return false;
        }
        
        return false;
    }

    protected static function getNormalizedRoute($requestMethod, $requestedMethod, Request $request)
    {
        //check 1 to 1 match first
        if(isset(self::$methods[$requestMethod][$requestedMethod])) {
            return $requestedMethod;
        }

        //we need to match our given route 
        $matchRoute = null;
        foreach(self::$methods[$requestMethod] as $apiMethod => $apiDetails)
        {
            //only match routes that have dynamic variables
            $result = preg_match(self::VALID_URI_REGEX, $apiMethod);
            if(!$result) {
                continue;
            }

            $routeParts = explode('/', $apiMethod);
            $requestedParts = explode('/', $requestedMethod);

            if(count($routeParts) !== count($requestedParts)) {
                continue;
            }

            $i = 0;
            $matched = true;

            foreach($requestedParts as $requestedPart) {
                //if we aren't a dynamic part of the url, check that we match 1 to 1
                if(!preg_match(self::VALID_URI_REGEX, $routeParts[$i])) {
                    if($requestedPart !== $routeParts[$i]) {
                        $matched = false;
                        break;
                    }
                } else {
                    $paramName = str_replace(["{", "}"], '', $routeParts[$i]);
                    $request->setUrlParam($paramName, $requestedPart);
                }

                $i++;
            }

            if(!$matched) {
                continue;
            }

            return $apiMethod;
        }

        return null;

    }

    /**
     * Set which API versions are enabled
     * @param array $versions
     */
    public static function versions($versions = array()) {
        self::$versions = $versions;
    }


    /**
     * Call the given version and method
     * @param string $api_version
     * @param string $api_method
     * @param Request $request
     * @return type
     */
    public static function call($api_version, $apiMethod, Request $request) {
        
        $requestMethod = strtolower($request->getServer()->get('REQUEST_METHOD'));

        $apiMethod = self::getNormalizedRoute($requestMethod, $apiMethod, $request);
        
        return self::$methods[$requestMethod][$apiMethod]->call($api_version, $request);
    }

    /**
     * Define errors
     * @param type $errors
     */
    public static function errors($errors = array()) {
        self::$errors = $errors;
    }

    /**
     * Require a parameter, and validate with callback
     */
    public static function validate($param_name, $validate = false, $arguments = array()) {
        array_unshift($arguments, $param_name);
        if (is_array($validate)) {
            foreach ($validate as $validate_callback) {
                if (false === ($value = call_user_func_array($validate_callback, $arguments))) {
                    throw new ApiException(Api::lastError());
                }
            }
            return $value;
        }
        if ($validate !== false && false === ($value = call_user_func_array($validate, $arguments))) {
            throw new ApiException(Api::lastError());
        }
        return $value;
    }

    /**
     * Set an API Error
     * @param type $msg
     */
    public static function error($msg) {
        self::$last_error = self::$errors[] = $msg;
    }

    /**
     * Get the last error
     * @return type
     */
    public static function lastError() {
        return self::$last_error;
    }

    /**
     * Clean/Retrieve input from POST
     * @param type $key
     */
    public static function input($key) {

        if (!filter_has_var(INPUT_POST, $key)) {
            return false;
        }

        return filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Calls a verify callback
     * @param string $name
     * @return boolean
     */
    public static function verify($name, $request) {
        if (isset(self::$verify[$name])) {
            $func = self::$verify[$name];
            return call_user_func($func, $request);
        }
        return false;
    }
    
    /**
     * Adds a verifier to the available list
     * @param string $name
     * @param callable $callable
     */
    public static function verifier($name, $callable) {
        self::$verify[$name] = $callable;
    }

    /**
     * Add a new API method
     * @param type $method_name
     * @return \ApiMethod
     */
    protected static function method($request_method, $method_name) {
        $method = new Method();
        $method->name = $method_name;
        if(!isset(self::$methods[$request_method])) {
            self::$methods[$request_method] = array();
        }
        self::$methods[$request_method][$method_name] = $method;

        return $method;
    }
}
