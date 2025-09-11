<?php

namespace App\Services;

class WalletService
{
    public function currentCurrency(): string
    {
        return $_SESSION['wallet_currency'] ?? 'EUR';
    }

    public function balanceMinor(int $clientId, ?string $cur = null): int
    {
        $currency = $cur ?? $this->currentCurrency();
        $row = db()->fetchOne(
            'SELECT balance_minor FROM wallets WHERE client_id = :u AND currency = :c', [':u'=>$clientId, ':c'=>$currency]
        );

        return (int)($row['balance_minor'] ?? 0);
    }

    public function formattedBalance(int $clientId, ?string $cur = null): string
    {
        $bal = $this->balanceMinor($clientId, $cur);
        return number_format($bal / 100, 2, '.', ' ') . ' ' . $this->currentCurrency();
    }

}