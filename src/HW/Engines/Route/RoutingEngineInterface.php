<?php

namespace HW\Engines\Route;


interface RoutingEngineInterface
{
    public function resolve($method, $reqUri);
}