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
            $this->notificationService
        );
    }

    public function testTransferSuccess()
    {
        $this->userModel->method('getUserById')
            ->willReturn(['id_user' => 1, 'type_name' => 'user']);

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

        $result = $this->transferService->transfer(1, 2, 100);

        $this->assertEquals(['message' => 'Transaction successful. Transaction ID: 1', 'code' => 200], $result);
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

    public function testTransferMerchant()
    {
        $this->userModel->method('getUserById')
            ->willReturn(['id_user' => 1, 'type_name' => 'merchant']);
        
        $this->walletModel->method('getPayerWallet')
            ->willReturn(['balance' => 1000]);
        
        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);
        
        $result = $this->transferService->transfer(1, 2, 100);
        
        $this->assertEquals('Merchants cannot send money.', $result['error']);
        $this->assertEquals(403, $result['code']);
    }

    public function testTransferPayerNotFound()
    {
        $this->userModel->method('getUserById')
            ->willReturn(['id_user' => 1, 'type_name' => 'common']);
        
        $this->walletModel->method('getPayerWallet')
            ->willReturn(null);
        
        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);
        
        $result = $this->transferService->transfer(1, 2, 100);
        
        $this->assertEquals('Payer Wallet not found.', $result['error']);
        $this->assertEquals(404, $result['code']);
    }
}
