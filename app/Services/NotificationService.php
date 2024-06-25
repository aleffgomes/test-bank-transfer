<?php

namespace App\Services;

use App\Interfaces\Services\NotificationServiceInterface;
use Predis\Client;

class NotificationService implements NotificationServiceInterface
{
    const QUEUE_NAME = 'notification_queue';
    const MAX_ATTEMPTS = 5;
    protected $client;
    protected $redis;

    public function __construct($client)
    {
        $this->client = $client;

        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => getenv('redis.default.host'),
            'port'   => getenv('redis.default.port'),
        ]);
    }

    public function sendNotification(int $userId, string $message, bool $addToQueue = true): bool
    {
        $data = [
            'user_id' => $userId,
            'message' => $message,
        ];

        try {
            $response = $this->client->request('POST', 'https://util.devi.tools/api/v1/notify', [
                'json' => $data,
            ]);

            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            if ($addToQueue) $this->addToQueue($data);
            return false;
        }
    }

    public function addToQueue(array $data): void
    {
        $data['attempts'] = !isset($data['attempts']) ? 1 : $data['attempts'] += 1;

        $this->redis->rpush(self::QUEUE_NAME, json_encode($data));
    }

    public function retryFailedNotifications(): void
    {
        $try = 0;
        $notification = $this->redis->lpop(self::QUEUE_NAME);
    
        while ($notification) {
            if ($try >= self::MAX_ATTEMPTS) return;

            $try++;

            $data = json_decode($notification, true);
    
            if (!$this->sendNotification($data['user_id'], $data['message'], false)) {
                $data['attempts'] = isset($data['attempts']) ? $data['attempts'] + 1 : 1;
                $this->redis->rpush(self::QUEUE_NAME, json_encode($data));
                echo 'Failed to send notification, re-queued.' . PHP_EOL;
            } else {
                $this->redis->ltrim(self::QUEUE_NAME, -1, 0);
                echo 'Notification sent.' . PHP_EOL;
            }
    
            $notification = $this->redis->lpop(self::QUEUE_NAME);
        }
    }
    
    
}
