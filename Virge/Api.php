<?php
namespace Virge;

use Virge\Api\Component\Method;
use Virge\Api\Exception\ApiException;
use Virge\Router\Component\Request;

/**
 * 
 * @author Michael Kramer
 */
class Api {

    protected static $methods = array();
    protected static $versions = array();
    protected static $errors = array();
    protected static $last_error = '';
    protected static $verify = array();
    /**
     * Add a new API method
     * @param type $method_name
     * @return \ApiMethod
     */
    public static function method($method_name) {
        $method = new Method();
        $method->name = $method_name;
        self::$methods[$method_name] = $method;

        return $method;
    }

    /**
     * Make sure a version of the API exists and is active
     * @param string $version
     * @param string $api_method
     */
    public static function check($version, $api_method) {
        if (in_array($version, self::$versions)) {
            //see if this api method exists for this specific version or for all
            if (isset(self::$methods[$api_method]) && self::$methods[$api_method]->canCall($version)) {
                return true;
            }
            return false;
        }
        return false;
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
    public static function call($api_version, $api_method, $request = null) {
        return self::$methods[$api_method]->call($api_version, $request);
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
    public static function verify($name) {
        if (isset(self::$verify[$name])) {
            $func = self::$verify[$name];
            return $func();
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

}
