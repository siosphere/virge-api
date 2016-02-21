<?php
namespace Virge\Api\Exception;

/**
 * 
 * @author Michael Kramer
 */
class ApiException extends \Exception {
    
    protected $data;
    
    protected $statusCode;
    
    public function __construct($message, $data = array(), $statusCode = 400, $code = null, $previous = null) {
        $this->data = $data;
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 
     * @return array
     */
    public function getData() {
        return $this->data;
    }
    
    public function getStatusCode() {
        return $this->statusCode;
    }


}
