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
        
        if(!Api::check($version, $method)) {
            $body = json_encode(array(
                "error"     =>      "Api method does not exist, or missing version"
            ));
        } else {
            try {
                //attempt to call it!
                $body = json_encode(Api::call($version, $method, $request));
                
            } catch (ApiException $ex) {
                
                $body = array(
                    "error"     =>      $ex->getMessage(),
                );
            }
        }
        
        $response = new Response($body);
        $response->addHeader('Content-Type: application/json');
        
        return $response;
    }
}