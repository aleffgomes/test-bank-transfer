<?php

namespace App\Interfaces\Services;
use CodeIgniter\HTTP\ResponseInterface;

interface NotificationServiceInterface
{
    public function sendNotification(int $userId, string $message, bool $addToQueue = true): bool;
    public function addToQueue(array $data): void;
    public function retryFailedNotifications(): void;
}
