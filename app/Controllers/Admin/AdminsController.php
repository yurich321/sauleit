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
        $client = repoAdmin()->findClientById($clientId);
        if(!$client) {
            $res->redirect('/panel/clients');
        }

        view()::render('details', [
            'client' => $client,
            'client_stakes' => repoAdmin()->getClientStakes($clientId),
        ]);
    }

}