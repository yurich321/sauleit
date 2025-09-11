<?php

namespace App\Core;

class Request
{

    private array $routeParams = [];
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return $uri ?: '/';
    }

    public function get(string $key, $def=null)
    {
        return $_GET[$key] ?? $def;
    }

    public function post(string $key, $def=null)
    {
        return trim($_POST[$key]) ?? $def;
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function header(string $name, $def=null)
    {
        $h = 'HTTP_' . strtoupper(str_replace('-','_',$name));
        return $_SERVER[$h] ?? $def;
    }

    public function _setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, $def=null)
    {
        return $this->routeParams[$key] ?? $def;
    }

}
