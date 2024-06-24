<?php

namespace App\Interfaces\Models;

interface WalletModelInterface
{
    public function getPayerWallet(int $userId);
    public function updateWalletBalances(int $payerId, int $payeeId, float $amount);
}
