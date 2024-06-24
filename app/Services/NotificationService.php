<?php

namespace App\Services;

use App\Interfaces\Services\NotificationServiceInterface;
use Predis\Client;

class NotificationService implements NotificationServiceInterface
{
    const QUEUE_NAME = 'notification_queue';
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

            return $response->getStatusCode() === 200;
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
        $notification = $this->redis->lpop(self::QUEUE_NAME);

        while ($notification) {
            $data = json_decode($notification, true);

            if (!$this->sendNotification($data['user_id'], $data['message'], false)) {
                $data['attempts'] += 1;
                $this->redis->rpush(self::QUEUE_NAME, json_encode($data));
            }

            $notification = $this->redis->lpop(self::QUEUE_NAME);
        }
    }
}
