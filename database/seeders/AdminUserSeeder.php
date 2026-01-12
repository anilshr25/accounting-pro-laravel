<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser\AdminUser;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $datas = [
            [
                'first_name' => 'Admin',
                'last_name' => 'admin',
                'email' => 'admin@accountingpro.com',
                'password' => bcrypt('Forgot911!'),
                'user_type' => 'admin',
                'address' => 'ktm',
                'mobile' => '9812345678',
                'is_active' => 1,
            ],
        ];

        foreach ($datas as $data) {
            $adminUser = AdminUser::whereEmail($data['email'])->first();
            if (empty($adminUser)) {
                AdminUser::create($data);
            }
        }
    }
}
