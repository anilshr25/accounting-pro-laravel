<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OwnerUser\OwnerUser;

class TbrothersOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'first_name' => 'T',
            'last_name' => 'Brothers',
            'email' => 'tbrothersenterprises3@gmail.com',
            'password' => bcrypt('Tbrothers@123'),
            'phone' => '4370616',
            'image' => 'default.png',
            'is_active' => true,
            'company_name' => 'T.brothers',
            'company_address' => 'Chunikhel, Budhanilkantha',
            'workplace' => 'tbrothers.accountingpro.local',
            'company_email' => 'info@tbrothers.com',
            'company_pan_no' => '600935726',
            'company_registration_no' => 'REG987654',
            'company_industry' => 'Enterprises',
            'company_country' => 'Nepal',
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
