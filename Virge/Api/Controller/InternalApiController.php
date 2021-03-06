<?php
namespace Virge\Api\Controller;

use Virge\Api\Component\InternalResponse;

/**
 * 
 * @author Michael Kramer
 */
class InternalApiController implements ApiControllerInterface
{
    public function _formatAPIResponse($responseData) : \Virge\Router\Component\Response
    {
        return new InternalResponse($responseData);
    }
}