<?php
namespace Virge\Api\Component;

use Virge\Router\Component\Request;

interface IMiddleWare
{
    public function apply(string $version, string $method, Request $request) : Request;
}