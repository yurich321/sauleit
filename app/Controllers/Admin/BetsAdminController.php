<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;

class BetsAdminController
{
    public function settle(Request $req, Response $res): void
    {
        $stakeId = (int)($req->post('stake_id') ?? 0);
        $result  = (string)($req->post('result') ?? ''); // 'win' | 'lose'

        if ($stakeId <= 0 || !in_array($result, ['win','lose'], true)) {
            $res->status(422)->json(['ok'=>false,'error'=>'Bad input']);
        }

        try {
            if ($result === 'lose') {
                repoAdmin()->settleLose($stakeId);
            } else {
                $wallet = repoAdmin()->settleWin($stakeId);
            }

            $res->status(200)->json(['ok' => true, 'result' => $result , 'wallet' => $wallet ?? null]);

        } catch (\Throwable $e) {
            logger()->error($e->getMessage());
            $res->status(500)->json(['ok' =>false, 'error' => 'Settlement failed']);
        }
    }

}