<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OwnerUser\OwnerUser;

class OwnerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'first_name' => 'Owner',
            'last_name' => 'User',
            'email' => 'admin@accountingpro.com',
            'password' => bcrypt('Forgot911!'),
            'phone' => '1234567890',
            'image' => 'default.png',
            'is_active' => true,
            'company_name' => 'T.mart',
            'company_address' => '123 Owner St',
            'workplace' => 'tmart.accountingpro.local',
            'company_email' => 'info@futechnepal.com',
            'company_pan_no' => 'PAN123456',
            'company_registration_no' => 'REG123456',
            'company_industry' => 'Technology',
            'company_country' => 'USA',
            'company_website' => 'https://ownercompany.com',
            'status' => 'approved',
            'approved_by' => 1,
            'remarks' => 'Initial owner user',
        ];

        OwnerUser::updateOrCreate(
            ['email' => $data['email']],
            $data
        );
    }
}
