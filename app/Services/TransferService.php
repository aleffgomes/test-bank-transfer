<?php

namespace App\Services;

use App\Interfaces\Services\TransferServiceInterface;
use App\Interfaces\Services\AuthorizationServiceInterface;
use App\Interfaces\Services\NotificationServiceInterface;
use App\Interfaces\Models\UserModelInterface;
use App\Interfaces\Models\WalletModelInterface;
use App\Interfaces\Models\TransactionModelInterface;
use App\Interfaces\Models\TransactionStatusModelInterface;
use Exception;

class TransferService implements TransferServiceInterface
{
    const TYPE_MERCHANT = 'merchant';
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const CURRENCY = 'BRL';

    protected $userModel;
    protected $walletModel;
    protected $transactionModel;
    protected $transactionStatusModel;
    protected $authorizationService;
    protected $notificationService;

    public function __construct(
        UserModelInterface $userModel,
        WalletModelInterface $walletModel,
        TransactionModelInterface $transactionModel,
        TransactionStatusModelInterface $transactionStatusModel,
        AuthorizationServiceInterface $authorizationService,
        NotificationServiceInterface $notificationService
    ) {
        $this->userModel = $userModel;
        $this->walletModel = $walletModel;
        $this->transactionModel = $transactionModel;
        $this->transactionStatusModel = $transactionStatusModel;
        $this->authorizationService = $authorizationService;
        $this->notificationService = $notificationService;
    }

    public function transfer(int $payerId, int $payeeId, float $amount): array
    {
        if(!$this->authorizationService->checkAuthorization()) {
            return ['error' => 'Authorization failed.', 'code' => 401];
        }

        $payer = $this->userModel->getUserById($payerId);
        $payee = $this->userModel->getUserById($payeeId);

        if (!$payer || !$payee) return ['error' => 'Payer or Payee not found.', 'code' => 404];

        if ($payerId == $payeeId) return ['error' => 'You cannot send money to yourself.', 'code' => 403];

        $payerWallet = $this->walletModel->getPayerWallet($payerId);
        
        if ($payer['type_name'] == self::TYPE_MERCHANT) {
            return ['error' => 'Merchants cannot send money.', 'code' => 403];
        }

        if ($payerWallet['balance'] < $amount) {
            return [
                'error' => 'Insufficient balance. Your balance is: ' . $payerWallet['balance'] . ' ' . self::CURRENCY, 
                'code' => 403
            ];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_PENDING);
        $transactionId = $this->transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);

        if (!$transactionId) return ['error' => 'Transaction failed.', 'code' => 500];

        $walletUpdate = $this->walletModel->updateWalletBalances($payerId, $payeeId, $amount);

        if (!$walletUpdate) {
            $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_FAILED);
            $this->transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);

            return ['error' => 'Transaction failed.', 'code' => 500];
        }

        $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_COMPLETED);
        $this->transactionModel->updateTransactionStatus($transactionId, $statusId);

        $notificationSent = $this->notificationService->sendNotification((int) $payee['id_user'], 'Payment received.');

        if (!$notificationSent) {
            $this->notificationService->addToQueue([
                'user_id' => (int) $payee['id_user'],
                'message' => 'Payment received.'
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) return ['error' => 'Transaction failed.', 'code' => 500];

        return ['message' => "Transaction successful. Transaction ID: $transactionId", 'code' => 200];

    }
}
