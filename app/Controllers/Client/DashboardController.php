<?php

namespace App\Controllers\Client;

class DashboardController
{

    public function index()
    {
        view()::render('dashboard', ['data' => []]);
    }

}