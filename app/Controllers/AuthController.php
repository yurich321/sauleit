<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\DTO\UserDTO;

final class AuthController
{
    public function loginForm(Request $req, Response $res): void
    {
        //$res->view('auth/login');
        $res->view('login');
    }

    public function clientLogin(Request $req, Response $res): never
    {
        $email    = $req->post('email');
        $password = $req->post('password');

        $user = authClient()->authenticate($email, $password);

        if (!$user) {
            $res->redirect('/');
        }

        $res->redirect(home()->afterLogin($user));
    }

    public function clientLogout(Request $req, Response $res): never
    {
        authClient()->logout();
        $res->redirect('/');
    }

    public function panelLogin(Request $req, Response $res): never
    {
        $email    = $req->post('email');
        $password = $req->post('password');

        $user = authPanel()->authenticate($email, $password);

        if (!$user) {
            $res->redirect('/panel');
        }

        $res->redirect(home()->afterLogin($user));
    }

    public function panelLogout(Request $req, Response $res): never
    {
        authPanel()->logout();
        $res->redirect('/panel');
    }
}
