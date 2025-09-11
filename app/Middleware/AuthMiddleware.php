<?php

namespace App\Middleware;
use App\DTO\UserDTO;

final class AuthMiddleware
{
    public static function require(string $ctx, ?string $guestRedirect = null): callable
    {
        $guestRedirect ??= ($ctx === 'panel' ? '/panel' : '/');

        return function ($req, $res, $next) use ($ctx, $guestRedirect) {
            $user = self::getUser($ctx);

            if (!$user instanceof UserDTO) {
                return $res->redirect($guestRedirect);
            }

            return $next($req, $res);
        };
    }

    public static function guestOnly(string $ctx): callable
    {
        return function ($req, $res, $next) use ($ctx) {
            $user = self::getUser($ctx);

            if ($user instanceof UserDTO) {
                return $res->redirect(home()->afterLogin($user));
            }

            return $next($req, $res);
        };
    }

    private static function getUser(string $ctx): ?UserDTO
    {
        return $ctx === 'panel' ? authPanel()->user() : authClient()->user();
    }
}