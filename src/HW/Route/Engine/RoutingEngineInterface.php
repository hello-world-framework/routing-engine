<?php

namespace HW\Route\Engine;


interface RoutingEngineInterface
{
    public function resolve($method, $reqUri);
}