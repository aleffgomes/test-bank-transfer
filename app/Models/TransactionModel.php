<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id_transaction';
    protected $allowedFields = ['payer_id', 'payee_id', 'amount', 'status_id'];
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
     * Save a transaction.
     *
     * @param int $payerId The ID of the payer.
     * @param int $payeeId The ID of the payee.
     * @param float $amount The amount of the transaction.
     * @param int $statusId The ID of the transaction status.
     * @return int The ID of the saved transaction.
     */
    public function saveTransaction(int $payerId, int $payeeId, float $amount, int $statusId): int
    {
        return $this->save([
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'amount' => $amount,
            'status_id' => $statusId
        ]);
    }
}
