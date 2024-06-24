<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\TransferService;
use App\Interfaces\Services\AuthorizationServiceInterface;
use App\Interfaces\Services\NotificationServiceInterface;
use App\Interfaces\Models\UserModelInterface;
use App\Interfaces\Models\WalletModelInterface;
use App\Interfaces\Models\TransactionModelInterface;
use App\Interfaces\Models\TransactionStatusModelInterface;
use Exception;

class TransferServiceTest extends CIUnitTestCase
{
    protected $transferService;
    protected $userModel;
    protected $walletModel;
    protected $transactionModel;
    protected $transactionStatusModel;
    protected $authorizationService;
    protected $notificationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->userModel = $this->createMock(UserModelInterface::class);
        $this->walletModel = $this->createMock(WalletModelInterface::class);
        $this->transactionModel = $this->createMock(TransactionModelInterface::class);
        $this->transactionStatusModel = $this->createMock(TransactionStatusModelInterface::class);
        $this->authorizationService = $this->createMock(AuthorizationServiceInterface::class);
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);

        $this->transferService = new TransferService(
            $this->userModel,
            $this->walletModel,
            $this->transactionModel,
            $this->transactionStatusModel,
            $this->authorizationService,
            $this->notificationService
        );
    }

    public function testTransferSuccess()
    {
        // Configurar os mocks para este teste específico
        $this->userModel->method('getUserById')
            ->willReturn(['id_user' => 1, 'type_name' => 'user']); // Payer

        $this->walletModel->method('getPayerWallet')
            ->willReturn(['balance' => 1000]);

        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);

        $this->transactionStatusModel->method('getStatusId')
            ->willReturn(1);

        $this->transactionModel->method('saveTransaction')
            ->willReturn(1);

        $this->walletModel->method('updateWalletBalances')
            ->willReturn(true);

        $this->notificationService->method('sendNotification')
            ->willReturn(true);

        // Executar o método a ser testado
        $result = $this->transferService->transfer(1, 2, 100);

        // Asserts para verificar o resultado esperado
        $this->assertEquals(['message' => 'Transaction successful. Transaction ID: 1'], $result);
    }
    
    public function testTransferInsufficientBalance()
    {
        $this->userModel->method('getUserById')
            ->willReturnOnConsecutiveCalls(
                ['id_user' => 1, 'type_name' => 'user'], // Payer
                ['id_user' => 2, 'type_name' => 'user']  // Payee
            );
    
        $this->walletModel->method('getPayerWallet')
            ->willReturn(['balance' => 50]);
    
        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);
    
        $result = $this->transferService->transfer(1, 2, 100);
    
        $this->assertEquals('Insufficient balance. Your balance is: 50 BRL', $result['error']);
        $this->assertEquals(403, $result['code']);
    }
    
}
