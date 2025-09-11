<?php

require __DIR__ . '/../vendor/autoload.php';

define('ROOT_DIR', realpath(__DIR__ . '/..'));

use App\Core\{Router, Request, Response};
use App\Middleware\{AuthMiddleware, CsrfMiddleware, LayoutMiddleware};
use App\Controllers\AuthController;
use App\Controllers\Client\{DashboardController, BetsController};
use App\Controllers\Admin\{AdminsController, BetsAdminController};

session_name('APPSESSID');
session_start();

// Error handling
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/php-error.log');


$router = new Router();

// add client routes
/*$router->add('GET','/', [AuthController::class,'loginForm'], [
        layout()->use('client'),]
);*/
$router->add('GET','/', [AuthController::class,'loginForm'], [
        layout()->use('login'),]
);

$router->add('POST','/login',  [AuthController::class,'clientLogin'],  [csrf()->verify(), AuthMiddleware::guestOnly('client')]);
$router->add('POST','/logout', [AuthController::class,'clientLogout'], [csrf()->verify(), AuthMiddleware::require('client')]);

// dashboard for clients
$router->add('GET','/dashboard', [DashboardController::class,'index'], [layout()->use('client'),
        AuthMiddleware::require('client'),]
);

// api endpoint clients
$router->add('POST', '/api/balance', [BetsController::class, 'apiBalance'], [
    csrf()->verify(), AuthMiddleware::require('client'),
]);
$router->add('POST', '/api/bet/place', [BetsController::class, 'apiPlaceBet'], [
    csrf()->verify(),
    AuthMiddleware::require('client'),
]);


// routes for admins/managers
$router->add('GET', '/panel',  function($req, $res) {

    if (authPanel()->user()) {
        //$res->redirect('/panel/clients');
        $res->redirect(home()->afterLogin(authPanel()->user()));
        exit;
    }

    return $res->view('auth/login');
}, [layout()->use('panel'),]);

$router->add('POST', '/panel/login', [AuthController::class, 'panelLogin'], [
    csrf()->verify(),
    AuthMiddleware::guestOnly('panel'),
]);
$router->add('POST', '/panel/logout', [AuthController::class, 'panelLogout'], [
    csrf()->verify(),
    AuthMiddleware::require('panel'),
]);

$router->group('/panel', [layout()->use('panel'), AuthMiddleware::require('panel'),], function(Router $r) {

    $r->add('GET', '/clients',        [AdminsController::class,'list']);
    $r->add('POST','/clients/update', [AdminsController::class,'update'], [csrf()->verify()]);
    $r->add('GET','/clients/{id}', [AdminsController::class,'clientDetails']);
    $r->add('POST','/bets/settle', [BetsAdminController::class,'settle'], [csrf()->verify()]);

});

// redirect /admin to /panel if entered
$router->add('GET','/admin', fn($req, $res) => $res->redirect('/panel'));

//print_info($_SESSION);

$req = new Request();
$res = new Response();

// run the router
$router->dispatch($req, $res);