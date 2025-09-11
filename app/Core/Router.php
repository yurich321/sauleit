<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function group(string $prefix, array $middleware, callable $define): void
    {
        $this->groupStack[] = ['prefix'=>$prefix, 'middleware'=>$middleware];
        $define($this);
        array_pop($this->groupStack);
    }

    public function add(string $method, string $path, $handler, array $middleware = []): void
    {
        $prefix = '';
        $groupMw = [];
        foreach ($this->groupStack as $g) {
            $prefix   .= rtrim($g['prefix'], '/');
            $groupMw   = array_merge($groupMw, $g['middleware']);
        }

        $fullPath = rtrim($prefix . '/' . ltrim($path, '/'), '/') ?: '/';

        // convert path with {param} to regex
        $regex = $this->pathToRegex($fullPath);

        // store the route with its method, path, handler, and middleware
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $fullPath,
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => array_merge($groupMw, $middleware),
        ];
    }

    public function dispatch(Request $req, Response $res)
    {
        $uri    = rtrim($req->path(), '/') ?: '/';
        $method = $req->method();

        foreach ($this->routes as $r) {

            if ($r['method'] !== $method) continue;

            if ($r['path'] === $uri) {
                return $this->run($r, $req, $res, []);
            }

            if (preg_match($r['regex'], $uri, $m)) {

                $params = array_filter($m, function ($k) {
                    return is_string($k);
                }, ARRAY_FILTER_USE_KEY);

                return $this->run($r, $req, $res, $params);
            }
        }

        return $res->status(404)->text('Not Found');
    }

    private function run(array $r, Request $req, Response $res, array $params) {

        $req->_setRouteParams($params);

        $next = function() use ($r, $req, $res) {
            if (is_array($r['handler'])) {
                [$class, $m] = $r['handler'];
                $obj = new $class();
                return $obj->$m($req, $res);
            }
            return call_user_func($r['handler'], $req, $res);
        };

        foreach (array_reverse($r['middleware']) as $mw) {
            $prev = $next;
            $next = fn() => $mw($req, $res, $prev);
        }

        return $next();
    }

    /**
     * Convert a path with {param} to a regex pattern
     * @param string $path
     * @return string
     */
    private function pathToRegex(string $path): string
    {
        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function($m){
                $name = $m[1];
                return '(?P<' . $name . '>[^/]+)';
            },
            $path
        );

        return '#^' . $pattern . '$#';
    }
}