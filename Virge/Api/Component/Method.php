<?php
namespace Virge\Api\Component;

use Virge\Api;
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
    protected $versions = array();
    protected $verifiers = array();

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
    public function verify($verifier) {
        $this->verifiers[] = $verifier;

        return $this;
    }

    /**
     * Call the method and return the results
     * @param type $version
     * @throws Exception
     */
    public function call($version) {
        if (!isset($this->versions['all']) && !isset($this->versions[$version])) {
            throw new \RuntimeException('Invalid method call');
        }
        if (!isset($this->versions[$version])) {
            $version = 'all';
        }

        if (!empty($this->verifiers)) {
            foreach ($this->verifiers as $verifier) {
                if (!Api::verify($verifier)) {
                    throw new \InvalidArgumentException('Invalid API Call');
                }
            }
        }

        $call = $this->versions[$version]['callback'];

        if (!is_callable($call)) {
            $func = $this->versions[$version]['method'];
            $controllerClassname = $call;
            $controller = new $controllerClassname;
            return $controller->$func();
        } else {
            return call_user_func($call);
        }
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
