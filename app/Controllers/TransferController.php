<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WalletModel;
use App\Models\TransactionModel;
use App\Models\TransactionStatusModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class TransferController extends ResourceController
{
    protected $modelName = 'App\Models\TransactionModel';
    protected $format    = 'json';

    /**
     * Transfer money from one user to another.
     *
     * @return \CodeIgniter\HTTP\Response The response indicating success or failure.
     */
    public function transfer(): \CodeIgniter\HTTP\Response
    {
        $rules = [
            'payer' => 'required|integer',
            'payee' => 'required|integer',
            'amount' => 'required|numeric',
        ];

        if (!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $requestData = $this->request->getJSON(true);

        $payerId = $requestData['payer'];
        $payeeId = $requestData['payee'];
        $amount = $requestData['amount'];

        $userModel = new UserModel();
        $walletModel = new WalletModel();
        $transactionModel = new TransactionModel();
        $TransactionStatusModel = new TransactionStatusModel();

        $payer = $userModel->getUserById($payerId);
        $payee = $userModel->getUserById($payeeId);

        print_r($payer); exit;

        if (!$payer || !$payee) return $this->failNotFound('Payer or Payee not found.');

        $payerWallet = $walletModel->getPayerWallet($payerId);

        if ($payerWallet['balance'] < $amount) return $this->fail('Insufficient balance.');

        if ($payer['user_type_id'] == 2) return $this->fail('Merchants cannot send money.');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if (!$this->checkAuthorization()) throw new Exception('Authorization failed.');

            $walletModel->updatePayerWalletBalance($payerWallet, $amount);
            $walletModel->updatePayeeWalletBalance($payeeId, $amount);

            $statusId = $TransactionStatusModel->getStatusId('Pending');

            $transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);

            $this->sendNotification((int) $payee['id_user'], 'Payment received.');

            $db->transComplete();
        } catch (Exception $e) {
            $db->transRollback();
            return $this->fail('Transaction failed: ' . $e->getMessage());
        }

        if (!$db->transStatus()) {
            return $this->fail('Transaction failed.');
        }

        return $this->respondCreated(['message' => 'Transaction successful.']);
    }

    /**
     * Check authorization.
     *
     * @return bool
     */
    private function checkAuthorization(): bool
    {
        $client = \Config\Services::curlrequest();
        $response = $client->request('GET', 'https://util.devi.tools/api/v2/authorize');

        return $response->getStatusCode() === 200;
    }

    /**
     * Send notification.
     *
     * @param int $userId The ID of the user to send the notification to.
     * @param string $message The content of the notification.
     * @return void
     */
    private function sendNotification(int $userId, string $message): void
    {
        $client = \Config\Services::curlrequest();
        $data = [
            'user_id' => $userId,
            'message' => $message,
        ];
        $client->request('POST', 'https://util.devi.tools/api/v1/notify', [
            'json' => $data,
        ]);
    }
}
