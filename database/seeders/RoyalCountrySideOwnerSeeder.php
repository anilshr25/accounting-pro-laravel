<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OwnerUser\OwnerUser;

class RoyalCountrySideOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'first_name' => 'Royal',
            'last_name' => 'Restaurant',
            'email' => 'rcsideresort@gmail.com',
            'password' => bcrypt('Royal@123'),
            'phone' => '9767484686',
            'image' => 'default.png',
            'is_active' => true,
            'company_name' => 'Royal Country Side Restaurant',
            'company_address' => 'Chunikhel, Budhanilkantha',
            'workplace' => 'royalcountryside.accountingpro.local',
            'company_email' => 'info@royalcountryside.com',
            'company_pan_no' => '609616152',
            'company_registration_no' => 'REG456789',
            'company_industry' => 'Restaurant',
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
