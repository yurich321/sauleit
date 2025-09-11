<?php

namespace App\Repositories;

class ClientsRepository
{

    private static ClientsRepository $instance;
    public static function getInstance(): ClientsRepository
    {
        if (!isset(self::$instance)) {
            self::$instance = new ClientsRepository();
        }
        return self::$instance;
    }

}