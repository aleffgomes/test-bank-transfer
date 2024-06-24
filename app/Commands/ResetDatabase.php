<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class ResetDatabase extends BaseCommand
{
    protected $group       = 'custom';
    protected $name        = 'db:reset';
    protected $description = 'Reset the database by dropping all tables and recreating them.';

    public function run(array $params)
    {
        $this->dropTables();
        $this->recreateTables();
        CLI::write('Database reset completed.', 'green');
    }

    protected function dropTables()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('information_schema.tables');

        $tables = $builder->select('table_name')
                         ->where('table_schema', $db->getDatabase())
                         ->get()
                         ->getResultArray();

        foreach ($tables as $table) {
            $tableName = $table['table_name'];
            try {
                $db->query("DROP TABLE IF EXISTS `$tableName`");
                CLI::write("Dropped table: $tableName", 'yellow');
            } catch (Exception $e) {
                CLI::error("Error dropping table $tableName: " . $e->getMessage());
            }
        }
    }

    protected function recreateTables()
    {
        $migrate = \Config\Services::migrations();
        $migrate->setSilent(false);
        $migrate->latest();
    }
}
