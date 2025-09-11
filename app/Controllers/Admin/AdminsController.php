<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;

class AdminsController
{
    public function list()
    {
        view()::render('panel', ['clientsList' => repoAdmin()->getClientsList()]);
    }

    public function clientDetails(Request $req, Response $res)
    {
        $id = $req->param('id');

        if ($id === null || !ctype_digit($id)) {
            $res->redirect('/panel/clients');
        }

        $clientId = (int)$id;

        view()::render('details', [
            'client' => repoAdmin()->findClientById($clientId),
            'client_stakes' => repoAdmin()->getClientStakes($clientId),
        ]);
    }

}