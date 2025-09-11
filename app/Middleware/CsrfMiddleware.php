<?php

namespace App\Middleware;

class CsrfMiddleware
{
    private static CsrfMiddleware $instance;

    public static function getInstance(): CsrfMiddleware
    {
        if (!isset(self::$instance)) {
            self::$instance = new CsrfMiddleware();
        }
        return self::$instance;
    }

    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
        }

        return $_SESSION['_csrf'];
    }

    public function verify(): callable
    {
        return function ($req, $res, $next) {
            $token = $_POST['_csrf'] ?? $req->header('X-CSRF-Token', '');
            $ok = $token && hash_equals($_SESSION['_csrf'] ?? '', $token);

            if (!$ok) {
                return $res->status(419)->text('validation failed');
            }

            return $next();
        };
    }

    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_csrf" value="'.htmlspecialchars($token,ENT_QUOTES,'UTF-8').'">';
    }
}