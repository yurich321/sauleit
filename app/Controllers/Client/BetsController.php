<?php

namespace App\Controllers\Client;
use App\Core\Request;
use App\Core\Response;

final class BetsController
{
    public function apiBalance(Request $req, Response $res): void
    {
        $user = authClient()->user();
        $cur  = (string)($req->post('currency') ?? 'EUR');

        // save current currency in session for wallet service
        $_SESSION['wallet_currency'] = $cur;

        $res->status(200)->json([
            'ok'      => true,
            'currency' => currencySymbol($cur),
            'wallet' => wallet()->formattedBalance($user->id, $cur),
        ]);
    }

    public function apiPlaceBet(Request $req, Response $res)
    {
        $user = authClient()->user();
        $currency = (string)$req->post('currency', 'EUR');
        $stake    = (float)$req->post('stake');
        $eventId  = (string)$req->post('event_id', '');
        $outcome  = (string)$req->post('outcome', '');
        $outcome_key  = (string)$req->post('outcome_key', '');

        if (!($stake >= 1 && $stake <= 500)) {
            $res->status(422)->json(['ok' => false,'error' => 'Stake must be between 1 and 500']);
        }
        $stakeMinor = (int)round($stake * 100);


        try {
            $result = db()->transaction(function () use ($user, $currency, $stakeMinor, $eventId, $outcome, $outcome_key) {
                // block wallet row for update
                $wallet = db()->fetchOne('SELECT * FROM wallets WHERE client_id = :uid AND currency = :cur FOR UPDATE', [
                    ':uid'=> $user->id, ':cur' => $currency
                ]);

                //TODO: if no wallet?

                // check balance
                $current = (int)$wallet['balance_minor'];
                $after   = $current - $stakeMinor;

                if ($after < 0) {
                    throw new \DomainException('Insufficient funds');
                }

                // set new balance
                db()->execute('UPDATE wallets SET balance_minor = :b, VERSION=VERSION+1 WHERE id=:id', [
                    ':b' => $after, ':id' => $wallet['id']
                ]);

                // audit log
                db()->insert('INSERT INTO wallet_tx (wallet_id, type, amount_minor, balance_after, meta_json)
                          VALUES (:w,"stake", :a, :ba, :m)', [
                    ':w' => $wallet['id'],
                    ':a' => $stakeMinor,
                    ':ba' => $after,
                    ':m' => json_encode(['event_id' => $eventId, 'outcome' => $outcome, 'outcome_key' => $outcome_key], JSON_UNESCAPED_UNICODE),
                ]);

                return $after;

            });

            $res->status(200)->json(['ok' => true, 'balance_minor' => number_format($result / 100, 2, '.', ' ') . ' ' . $currency]);

        } catch (\DomainException $e) {

            $res->status(422)->json(['ok'=>false,'error'=>$e->getMessage()]);
        } catch (\Throwable $e) {
            logger()->error($e->getMessage());
            $res->status(500)->json(['ok' => false,'error'=>'Server error']);
        }

    }

}