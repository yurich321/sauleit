<?php

use App\Core\Logger;
use App\Services\{LoginRedirect, View, WalletService};
use App\Middleware\{CsrfMiddleware, LayoutMiddleware};
use App\Core\Database;
use App\Repositories\{AdminRepository, ClientsRepository};
use App\Services\Auth\{ClientAuthService, PanelAuthService};


if (!function_exists('csrf')) {
    function csrf(): CsrfMiddleware
    {
        return CsrfMiddleware::getInstance();
    }
}

if (!function_exists('print_info')) {
    function print_info(...$array): void
    {
        //echo '<pre style="background: #f4f4f4; border: 1px solid #ddd; padding: 10px; margin: 10px 0; font-size: 14px;">';
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}

if (!function_exists('layoutMiddleware')) {
    function layout(): LayoutMiddleware
    {
        return LayoutMiddleware::getInstance();
    }
}

if (!function_exists('home')) {
    function home(): LoginRedirect
    {
        return LoginRedirect::getInstance();
    }
}

if (!function_exists('db')) {

    function db(): Database {
        static $db = null;
        if ($db === null) {
            $config = require __DIR__ . '/../../config/app.php';

            $dsn  = $config['DB_DSN']  ?? 'mysql:host=127.0.0.1;dbname=app;charset=utf8mb4';
            $user = $config['DB_USER'] ?? 'root';
            $pass = $config['DB_PASS'] ?? '';

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
            ]);

            $db = new Database($pdo);
        }
        return $db;
    }
}

if (!function_exists('view')) {

    function view(): View
    {
        return View::getInstance();

    }
}

if (!function_exists('repoAdmin')) {

    function repoAdmin(): AdminRepository
    {
        return AdminRepository::getInstance();

    }
}

if (!function_exists('repoClient')) {

    function repoClient(): ClientsRepository
    {
        return ClientsRepository::getInstance();

    }
}

if (!function_exists('authClient')) {
    function authClient(): ClientAuthService
    {
        static $instance;
        return $instance ??= new ClientAuthService();
    }
}

if (!function_exists('authPanel')) {
    function authPanel(): PanelAuthService
    {
        static $instance;
        return $instance ??= new PanelAuthService();
    }
}

if (!function_exists('logger')) {

    function logger(): Logger
    {
        return Logger::getInstance();
    }
}

if (!function_exists('events')) {

    function events(): array
    {
        return require ROOT_DIR . '/config/events.php';
    }
}

if (!function_exists('event_odds')) {

    function event_odds(string $eventId, string $outcomeKey): ?float
    {
        $event = events()[$eventId] ?? null;
        if (!$event) {
            return null;
        }
        $node = $event['outcomes'][$outcomeKey]['odds'] ?? null;
        return $node !== null ? (float)$node : null;
    }
}


if (!function_exists('wallet')) {

    function wallet(): WalletService
    {
        static $ws;
        return $ws ??= new WalletService();
    }
}

if (!function_exists('currencySymbol')) {
    function currencySymbol(string $cur): string
    {
        return match ($cur) {
            'EUR' => '€',
            'USD' => '$',
            'RUB' => '₽',
            default => $cur
        };
    }
}

