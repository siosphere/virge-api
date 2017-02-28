<?php
namespace Virge\Api\Controller;

use Virge\Router\Component\Response;

interface ApiControllerInterface
{
    public function _formatAPIResponse($resultData) : Response;
}