<?php
namespace Virge\Api\Controller;

use Virge\Api;
use Virge\Api\Exception\ApiException;
use Virge\Router\Component\Request;
use Virge\Router\Component\Response;

/**
 * 
 * @author Michael Kramer
 */
class ApiController {
    
    /**
     * Our entry point!
     * @param Request $request
     * @return Response
     */
    public function entry(Request $request) {
        //get my schtuff from the api
        //get version
        $version = $request->getUrlParam('v');
        $method = $request->getUrlParam($version);
        
        $statusCode = 200;
        
        if(!Api::check($version, $method)) {
            $body = json_encode(array(
                "error"     =>      "Api method does not exist, or missing version"
            ));
        } else {
            try {
                //attempt to call it!
                $body = json_encode(Api::call($version, $method, $request));
                
            } catch (ApiException $ex) {
                
                if($ex->getData()) {
                    $body = json_encode($ex->getData());
                } else {
                    $body = json_encode(array(
                        "error"     =>      $ex->getMessage(),
                    ));
                }
                
                $statusCode = $ex->getStatusCode();
            }
        }
        
        $response = new Response($body, $statusCode);
        $response->addHeader('Content-Type: application/json');
        return $response;
    }
}