<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Interfaces\Models\WalletModelInterface;

class WalletModel extends Model implements WalletModelInterface
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
     * Update the balances of the wallets of the given payer and payee.
     *
     * @param int $payerId The ID of the payer.
     * @param int $payeeId The ID of the payee.
     * @param float $amount The amount to update the balances by.
     * @return bool Whether the update was successful.
     */
    public function updateWalletBalances(int $payerId, int $payeeId, float $amount): bool
    {
        $this->where('user_id', $payerId)
            ->set('balance', 'balance - ' . $amount, false)
            ->update();
            
        $this->where('user_id', $payeeId)
            ->set('balance', 'balance + ' . $amount, false)
            ->update();

        return true;
    }
}
