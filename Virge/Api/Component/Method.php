<?php
namespace Virge\Api\Component;

use Virge\Api;
use Virge\Api\Exception\ApiException;
use Virge\Api\Component\Method\Verifier;
use Virge\Api\Controller\ApiControllerInterface;
use Virge\Router\Component\Request;
use Virge\Virge;

/**
 *
 * @author Michael Kramer
 */
class Method extends \Virge\Core\Model {

    /**
     *
     * @var type 
     */
    protected $versions = [];
    protected $verifiers = [];

    /**
     * Set a version of the method to a callback
     * @param type $version
     * @param type $callback
     * @return \Virge\Api\Component\Method
     */
    public function version($version, $callback, $method = null) {
        $this->versions[$version] = array(
            'callback' => $callback,
            'method' => $method
        );
        return $this;
    }
    
    /**
     * @param string $verifier
     * @return \Virge\Api\Component\Method
     */
    public function verify(string $verifier, $additionalParams = []) 
    {
        $this->verifiers[] = new Verifier($verifier, $additionalParams);

        return $this;
    }

    /**
     * Call the method and return the results
     * @param type $version
     * @param Request $request
     * @throws Exception
     */
    public function call($version, $request = null) 
    {
        if (!isset($this->versions['all']) && !isset($this->versions[$version])) {
            throw new ApiException('Invalid method call');
        }
        if (!isset($this->versions[$version])) {
            $version = 'all';
        }

        if (!empty($this->verifiers)) {
            foreach ($this->verifiers as $verifier) {
                if (!Api::verify($verifier, $request)) {
                    throw new ApiException('Invalid API Call');
                }
            }
        }

        $call = $this->versions[$version]['callback'];

        if (!is_callable($call)) {
            $func = $this->versions[$version]['method'];
            $controllerClassname = $call;
            $controller = new $controllerClassname;
            $result = call_user_func_array([$controller, $func], [$request]);
            if($controller instanceof ApiControllerInterface) {
                return call_user_func_array([$controller, '_formatAPIResponse'], [$result]);
            }

            return $result;
        }

        return call_user_func_array($call, array($request));
    }

    /**
     * Check if we can call this method for a specific version
     * @param string $version
     * @return boolean
     */
    public function canCall($version) {
        if (isset($this->versions['all']) || isset($this->versions[$version])) {
            return true;
        }
        return false;
    }

}
