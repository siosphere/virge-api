<?php
namespace Virge\Api\Component;

use Virge\Router\Component\Request;

abstract class BaseMiddleWare implements IMiddleWare
{
    public function apply(string $version, string $method, Request $request) : Request
    {
        return $request;
    }
}