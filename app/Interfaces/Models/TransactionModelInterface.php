<?php

namespace App\Interfaces\Models;

interface TransactionModelInterface
{
    public function saveTransaction(int $payerId, int $payeeId, float $amount, int $statusId);
    public function updateTransactionStatus(int $transactionId, int $statusId);
}
