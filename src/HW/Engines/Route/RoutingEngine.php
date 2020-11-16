<?php

namespace HW\Engines\Route;


class RoutingEngine implements RoutingEngineInterface
{
    private $routes;

    public function __construct($routes=[])
    {
        $this->routes = $routes;
    }

    private function filterUri($uri)
    {
        $uri = trim($uri);
        $len = strlen($uri);
        if($len > 0 && $uri[$len-1] === "/") {
            $uri = substr($uri, 0, $len-1);
            $len--;
        }
        if($len > 0 && $uri[0] === "/") {
            $uri = substr($uri, 1);
        }
        return $uri;
    }

    private function parse($uri, $opt)
    {
        $toks = explode("/", $uri);
        $uriInfo = [];
        foreach($toks as $tok) {
            $tok = trim($tok);
            $pat = "";
            $tokInfo = [];
            $len = strlen($tok);
            if($len > 0 && $tok[0] === "{") {
                if($tok[$len - 1] !== "}") {
                    throw new \Exception(
                        "Error Parsing Routes: parameter format's not correct"
                    );
                }
                $tok = substr($tok, 0, $len-1);
                $tok = substr($tok, 1);
                if(isset($opt["filter"])
                    && isset($opt["filter"][$tok])
                ) {
                    $pat = $opt["filter"][$tok];
                }
                $tokInfo["type"] = "param";
            } else {
                $tokInfo["type"] = "context";
            }
            $tokInfo["name"] = $tok;
            if($pat !== "") {
                $tokInfo["pat"] = $pat;
            }
            $uriInfo[] = $tokInfo;
        }
        return $uriInfo;
    }

    private function match($uri, $reqUri)
    {
        $ul = count($uri);
        $rl = count($reqUri);
        if($ul !== $rl) {
            return false;
        }
        $params = [];
        for($i=0; $i<$ul; $i++) {
            $tok = $uri[$i];
            $reqUri[$i] = trim($reqUri[$i]);
            if($tok["type"] === "context") {
                if($tok["name"] !== $reqUri[$i]) {
                    return false;
                }
            } else {
                if(isset($tok["pat"])) {
                    $pat = "/^" . $tok["pat"] . "$/";
                    if(!preg_match($pat, $reqUri[$i])) {
                        return false;
                    }
                }
                $params[] = $reqUri[$i];
            }
        }
        return $params;
    }

    public function resolve($method, $reqUri)
    {
        $method = strtoupper($method);
        $reqUri = explode("/", $this->filterUri($reqUri));
        foreach($this->routes[$method] as $uri => $opt) {
            $uri = $this->parse($this->filterUri($uri), $opt);
            $params = $this->match($uri, $reqUri);
            if($params !== false) {
                return [
                    "controller" => $opt["target"],
                    "params" => $params
                ];
            }
        }
        return null;
    }
}
