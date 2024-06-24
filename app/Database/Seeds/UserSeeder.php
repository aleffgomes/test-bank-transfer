<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'cpf_cnpj' => $faker->cpf,
                'user_type_id' => 1,
            ];
        }

        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'name' => $faker->company,
                'email' => $faker->unique()->companyEmail,
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'cpf_cnpj' => $faker->cnpj,
                'user_type_id' => 2, 
            ];
        }

        $this->db->table('users')->insertBatch($users);
    }
}
