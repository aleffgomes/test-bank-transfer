<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Exception;
use Config\Services;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'App Transfer API',
    attachables: [new OA\Attachable()]
)]

class TransferController extends ResourceController
{
    protected $modelName = 'App\Models\TransactionModel';
    protected $format    = 'json';

    protected $transferService;

    public function __construct()
    {
        $this->transferService = Services::transferService();
    }

    // Documentation
    #[OA\Post(path: '/transfer', summary: 'Transfer money from one user to another')]
    #[OA\RequestBody(required: true, description: 'Transfer money from one user to another', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'payer', type: 'integer', description: 'Payer user id'),
            new OA\Property(property: 'payee', type: 'integer', description: 'Payee user id'),
            new OA\Property(property: 'amount', type: 'number', format: 'float', description: 'Amount to transfer'),
        ],
        required: ['payer', 'payee', 'amount']
    ))]

    #[OA\Response(response: 200, description: 'Transfer money from one user to another')]
    #[OA\Response(response: 400, description: 'Bad Request')]
    #[OA\Response(response: 500, description: 'Internal Server Error')]
    // End of documentation
    
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

        try {
            $result = $this->transferService->transfer($payerId, $payeeId, $amount);

            return $this->respond(['messages' => $result, 'status' => $result['code']], $result['code']);
        } catch (Exception $e) {
            return $this->fail($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

}
