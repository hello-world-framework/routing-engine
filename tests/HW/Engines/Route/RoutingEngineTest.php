<?php

namespace Tests\HW\Engines\Route;


use PHPUnit\Framework\TestCase;
use HW\Engines\Route\RoutingEngineInterface;
use HW\Engines\Route\RoutingEngine;


class RoutingEngineTest extends TestCase
{
    public function testInterface()
    {
        $engine = new RoutingEngine([]);
        $this->assertTrue($engine instanceof RoutingEngineInterface);
    }

    public function testResolve()
    {
        $routes = [
            "GET" => [
                "users/" => [
                    "name" => "users.all",
                    "target" => ["users", "index"]
                ],
                "/api/users/{id}" => [
                    "name" => "users.show",
                    "target" => ["api.users", "show"],
                    "filter" => [
                        // "id" => "[A-Za-z0-9_-]+"

                        // allow only digits and
                        // there should be atleast one digit
                        "id" => "[0-9]+"
                    ]
                ]
            ],

            "POST" => [
                "users/create" => [
                    "name" => "users.create",
                    "target"=> ["users", "create"]
                ]
            ]
        ];

        $routingEngine = new RoutingEngine($routes);

        $uri = "/api/users/1234";
        $expected = [
            "controller" => ["api.users", "show"],
            "params" => ["1234"]
        ];
        $ret = $routingEngine->resolve("GET", $uri);
        $this->assertEquals($ret["params"], $expected["params"]);
        $this->assertEquals($ret["controller"], $expected["controller"]);

        $ret = $routingEngine->resolve("POST", $uri);
        $this->assertEquals($ret, null);

        
        // this would return null for this path
        // cause, 2wab contains char, but [0-9]+ is used as filter
        $uri = "/api/users/2wab/";
        $ret = $routingEngine->resolve("GET", $uri);
        $this->assertEquals($ret, null);

        $uri = "users/create";
        $expected = [
            "controller" => ["users", "create"],
            "params" => []
        ];
        $ret = $routingEngine->resolve("POST", $uri);
        $this->assertEquals($ret["params"], $expected["params"]);
        $this->assertEquals($ret["controller"], $expected["controller"]);
    }
}