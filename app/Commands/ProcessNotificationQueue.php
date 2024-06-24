<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ProcessNotificationQueue extends BaseCommand
{
    protected $group = 'Custom';
    protected $name = 'queue:process';
    protected $description = 'Process the notification queue.';

    public function run(array $params)
    {
        $notificationService = service('notificationService');
        $notificationService->retryFailedNotifications();

        CLI::write('Notification queue processed.', 'green');
    }
}
