<?php

namespace App\Repositories;
class AdminRepository
{
    private static AdminRepository $instance;
    public static function getInstance(): AdminRepository
    {
        if (!isset(self::$instance)) {
            self::$instance = new AdminRepository();
        }
        return self::$instance;
    }

    public function getClientsList()
    {
        return db()->fetchAll("SELECT
                                        c.id, c.name, c.gender, c.birth_date, c.email,
                                  SUBSTRING_INDEX(
                                    GROUP_CONCAT(CASE WHEN cc.type='phone' THEN cc.value END ORDER BY cc.id ASC SEPARATOR ','),
                                    ',', 1
                                  ) AS phone,
                                  SUBSTRING_INDEX(
                                    GROUP_CONCAT(CASE WHEN cc.type='email' THEN cc.value END ORDER BY cc.id ASC SEPARATOR ','),
                                    ',', 1
                                  ) AS contact_email
                                FROM clients c
                                LEFT JOIN client_contacts cc ON cc.client_id = c.id
                                GROUP BY c.id, c.name, c.gender, c.birth_date
                                ORDER BY c.id DESC;");
    }


    public function findClientById(int $id): ?array
    {
        $sql = "SELECT 
                    c.id,
                    c.email,
                    c.name,
                    c.is_active,
                    c.gender,
                    c.birth_date,
                    c.created_at,
                    cc.type  AS contact_type,
                    cc.value AS contact_value,
                    w.currency,
                    w.balance_minor
                FROM clients c
                LEFT JOIN client_contacts cc ON c.id = cc.client_id
                LEFT JOIN wallets w          ON c.id = w.client_id
                WHERE c.id = :client_id";


        $rows = db()->fetchAll($sql, [':client_id' => $id]);

        if (!$rows) {
            return null;
        }

        $client = [
            'id' => $rows[0]['id'],
            'email' => $rows[0]['email'],
            'name' => $rows[0]['name'],
            'is_active' => (bool)$rows[0]['is_active'],
            'gender' => $rows[0]['gender'],
            'birth_date' => $rows[0]['birth_date'],
            'created_at' => $rows[0]['created_at'],
            'wallets'  => [],
        ];

        $contacts = [];
        $seen = [];

        foreach ($rows as $row) {
            if ($row['contact_type'] !== null && $row['contact_value'] !== null) {
                $key = $row['contact_type'].'|'.$row['contact_value'];
                if (!isset($seen[$key])) {
                    $contacts[] = ['type' => $row['contact_type'], 'value' => $row['contact_value']];
                    $seen[$key] = true;
                }
            }

            if ($row['currency']) {
                $client['wallets'][$row['currency']] = $row['balance_minor'];
            }
        }
        $client['contacts'] = $contacts;

        return $client;

    }

    public function getClientStakes(int $clientId): array
    {
        $params = [':cid' => $clientId];

        $sql = "SELECT
                    tx.id,
                    tx.wallet_id,
                    w.currency,
                    tx.type,
                    tx.amount_minor,
                    tx.balance_after,
                    tx.meta_json,
                    tx.created_at
                FROM wallet_tx tx
                JOIN wallets w ON w.id = tx.wallet_id
                WHERE w.client_id = :cid
                  AND tx.type = 'stake'
                ORDER BY tx.created_at DESC, tx.id DESC
            ";

        $rows = db()->fetchAll($sql, $params) ?? [];

        return array_map(function ($r) {
            $meta = json_decode($r['meta_json'] ?? '', true) ?: [];

            $r['event_id']    = $meta['event_id']    ?? null;
            $r['outcome']     = $meta['outcome']     ?? null;
            $r['outcome_key'] = $meta['outcome_key'] ?? null;
            $r['result']      = $meta['result'] ?? null;
            $r['odds']        = event_odds($meta['event_id'], $meta['outcome_key']);

            unset($r['meta_json']);
            return $r;
        }, $rows);

    }

    /**
     * set stake as lost, no balance change, only update meta_json
     * @param int $stakeId
     * @return void
     * @throws \Throwable
     */
    public function settleLose(int $stakeId): void
    {
        db()->transaction(function() use ($stakeId) {

            $stake = db()->fetchOne("SELECT id, wallet_id, type, meta_json FROM wallet_tx WHERE id = :id FOR UPDATE", [
                ':id' => $stakeId
            ]);

            if (!$stake || $stake['type'] !== 'stake') {
                throw new \RuntimeException('Stake not found');
            }

            $meta = json_decode($stake['meta_json'] ?? '', true) ?: [];

            if (!empty($meta['result'])) {
                throw new \RuntimeException('Stake already settled');
            }

            $meta['result']     = 'lose';
            $meta['settled_at'] = date('Y-m-d H:i:s');

            db()->execute("UPDATE wallet_tx SET meta_json = :meta WHERE id = :id",
                [
                    ':meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                    ':id'   => $stakeId
                ]
            );
        });
    }

    /**
     * set stake as won, create payout tx and update wallet balance
     * @param int $stakeId
     * @param float $odds
     * @return void
     * @throws \Throwable
     */
    public function settleWin(int $stakeId): array
    {
       return db()->transaction(function() use ($stakeId) {

            // lock stake row
            $stake = db()->fetchOne("SELECT id, wallet_id, type, amount_minor, meta_json FROM wallet_tx WHERE id = :id FOR UPDATE",
                [':id' => $stakeId]
            );


            if (!$stake || $stake['type'] !== 'stake') {
                throw new \RuntimeException('Stake not found');
            }

            $meta = json_decode($stake['meta_json'] ?? '', true) ?: [];
            if (!empty($meta['result'])) {
                logger()->warning('Stake already settled');
                throw new \RuntimeException('Stake already settled');
            }

            // get odds from events config file
            $odds = event_odds($meta['event_id'], $meta['outcome_key']);

            $wallet = db()->fetchOne("SELECT id, balance_minor, version, currency FROM wallets WHERE id = :wid FOR UPDATE", [
                ':wid' => $stake['wallet_id']
            ]);

            if (!$wallet) {
                throw new \RuntimeException('Wallet not found');
            }

            $payout = (int) round(((int)$stake['amount_minor']) * $odds);
            if ($payout <= 0) {
                throw new \RuntimeException('Invalid payout amount');
            }

            $newBalance = (int)$wallet['balance_minor'] + $payout;

            $payoutMeta = [
                'kind'     => 'payout',
                'stake_id' => (int)$stake['id'],
            ];

            db()->insert("INSERT INTO wallet_tx (wallet_id, type, amount_minor, balance_after, meta_json)
             VALUES (:wid, 'payout', :amt, :bal_after, :meta)",
                [
                    ':wid'       => $wallet['id'],
                    ':amt'       => $payout,
                    ':bal_after' => $newBalance,
                    ':meta'      => json_encode($payoutMeta, JSON_UNESCAPED_UNICODE),
                ]
            );

            // update wallet balance
            db()->execute("UPDATE wallets SET balance_minor = :bal, version = version + 1 WHERE id = :wid",
                [':bal' => $newBalance, ':wid' => $wallet['id']]
            );

            // set stake as won
            $meta['result']     = 'win';
            $meta['settled_at'] = date('Y-m-d H:i:s');

            db()->execute("UPDATE wallet_tx SET meta_json = :meta WHERE id = :id",
                [
                    ':meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                    ':id'   => $stake['id']
                ]
            );


            return [
                'wallet_cur' => $wallet['currency'],
                'balance_minor' => $newBalance,
                'balance_text'   => number_format($newBalance/100, 2, '.', ' ') . ' ' . currencySymbol($wallet['currency']),
                ];
        });
    }


}