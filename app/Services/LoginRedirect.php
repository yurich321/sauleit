<?php

namespace App\Services;
use App\DTO\UserDTO;

final class LoginRedirect
{

    private static LoginRedirect $instance;

    public static function getInstance(): LoginRedirect
    {
        if (!isset(self::$instance)) {
            self::$instance = new LoginRedirect();
        }
        return self::$instance;
    }

    public function afterLogin(UserDTO $user): string
    {
        return $user->homePath ?: '/';
    }
}