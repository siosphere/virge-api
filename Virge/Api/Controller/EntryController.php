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
class EntryController 
{
    
    /**
     * Our entry point!
     * @param Request $request
     * @return Response
     */
    public function entry(Request $request) 
    {
        $version = $request->getUrlParam('v');
        
        $uri = $request->getURI();
        $method = str_replace('api/v/' . $version . '/', '', $uri);
        
        $statusCode = 200;
        try {
            if(!Api::check($version, $method, $request)) {
                throw new ApiException('Api method does not exist, or missing version');
            }
             //attempt to call it!
            $result = Api::call($version, $method, $request);
            if($result instanceof Response) {
                return $result;
            }

            $body = json_encode($result);
            
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

        
        $response = new Response($body, $statusCode);
        $response->addHeader('Content-Type: application/json');
        return $response;
    }
}
