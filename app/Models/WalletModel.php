<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    protected $table = 'wallets';
    protected $primaryKey = 'id_wallet';
    protected $allowedFields = ['user_id', 'balance'];
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get the wallet for a given payer ID.
     *
     * @param int $payerId The ID of the payer.
     * @return array|null The payer's wallet data or null if not found.
     */
    public function getPayerWallet(int $payerId): ?array
    {
        return $this->where('user_id', $payerId)->first();
    }

    /**
     * Update the balance of a payer's wallet.
     *
     * @param array $wallet The wallet data.
     * @param float $amount The amount to deduct from the balance.
     * @return void
     */
    public function updatePayerWalletBalance(array $wallet, float $amount): void
    {
        $wallet['balance'] -= $amount;
        $this->save($wallet);
    }

    /**
     * Update the balance of a payee's wallet.
     *
     * @param int $payeeId The ID of the payee.
     * @param float $amount The amount to add to the balance.
     * @return void
     */
    public function updatePayeeWalletBalance(int $payeeId, float $amount): void
    {
        $wallet = $this->where('user_id', $payeeId)->first();
        $wallet['balance'] += $amount;
        $this->save($wallet);
    }
}
