<?php

namespace App\Middleware;
use App\Services\View;

class LayoutMiddleware
{

    private static LayoutMiddleware $instance;

    public static function getInstance(): LayoutMiddleware
    {
        if (!isset(self::$instance)) {
            self::$instance = new LayoutMiddleware();
        }
        return self::$instance;
    }


    public function use(string $area): callable
    {
        return function($req, $res, $next) use ($area) {
            View::setArea($area);
            return $next();
        };
    }
}