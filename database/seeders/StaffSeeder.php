<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('Qwertyuiop@1');
        $faker = Faker::create();

        $staffs = [
            [
                'staff_id' => 'TC00000001',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'admin@tutorialcenter.com',
                'phone' => '08020000001',
                'gender' => 'Male',
                'staff_role' => 'admin',
            ],
            [
                'staff_id' => 'TC00000002',
                'firstname' => 'Smith',
                'lastname' => 'Doe',
                'email' => 'tutor1@tutorialcenter.com',
                'phone' => '08020000002',
                'gender' => 'Male',
                'staff_role' => 'tutor',
            ],
            [
                'staff_id' => 'TC00000003',
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'tutor2@tutorialcenter.com',
                'phone' => '08020000003',
                'gender' => 'Female',
                'staff_role' => 'tutor',
            ],
            [
                'staff_id' => 'TC00000004',
                'firstname' => 'Oluwaseun',
                'lastname' => 'Okechukwu',
                'email' => 'adviser1@tutorialcenter.com',
                'phone' => '08020000004',
                'gender' => 'Male',
                'staff_role' => 'adviser',
            ],
            [
                'staff_id' => 'TC00000005',
                'firstname' => 'Aishat',
                'lastname' => 'Olanrewaju',
                'email' => 'adviser2@tutorialcenter.com',
                'phone' => '08020000005',
                'gender' => 'Female',
                'staff_role' => 'adviser',
            ],
            [
                'staff_id' => 'TC00000006',
                'firstname' => 'Chinedu',
                'lastname' => 'Olawumi',
                'email' => 'staff1@tutorialcenter.com',
                'phone' => '08020000006',
                'gender' => 'Male',
                'staff_role' => 'staff',
            ],
            [
                'staff_id' => 'TC00000007',
                'firstname' => 'Noimot',
                'lastname' => 'Nwosu',
                'email' => 'staff2@tutorialcenter.com',
                'phone' => '08020000007',
                'gender' => 'Female',
                'staff_role' => 'staff',
            ],
        ];

        foreach ($staffs as $staff) {
            Staff::updateOrCreate(
                ['email' => $staff['email']], // unique key
                [
                    'staff_id' => $staff['staff_id'],
                    'firstname' => $staff['firstname'],
                    'lastname' => $staff['lastname'],
                    'phone' => $staff['phone'],
                    'password' => $defaultPassword,
                    'gender' => $staff['gender'],
                    'staff_role' => $staff['staff_role'],
                    // 'date_of_birth' => Carbon::now()->subYears(30),
                    'date_of_birth' => $faker->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'verification_code' => null,
                    'verified' => true,
                    'status' => 'active',
                    'home_address' => '1, Tutorial Center Building, Lagos',
                ]
            );
        }

        // Optional: generate random demo staff
        // Staff::factory()->count(10)->create();
    }
}


// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\Staff;
// use Illuminate\Support\Facades\Hash;

// class StaffSeeder extends Seeder
// {
//     public function run(): void
//     {
//         // Create an admin
//         Staff::insert([
//             'staff_id' => 'TC00000001',
//             'firstname' => 'John',
//             'lastname' => 'Doe',
//             'email' => 'admin@tutorialcenter.com',
//             'phone' => '08020000001',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Male',
//             'staff_role' => 'admin',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // Create an admin
//         Staff::insert([
//             'staff_id' => 'TC00000001',
//             'firstname' => 'John',
//             'lastname' => 'Doe',
//             'email' => 'admin@tutorialcenter.com',
//             'phone' => '08020000001',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Male',
//             'staff_role' => 'admin',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // Create an tutor 1
//         Staff::insert([
//             'staff_id' => 'TC00000002',
//             'firstname' => 'Smith',
//             'lastname' => 'Doe',
//             'email' => 'tutor1@tutorialcenter.com',
//             'phone' => '08020000002',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Male',
//             'staff_role' => 'tutor',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // Create an tutor 2
//         Staff::insert([
//             'staff_id' => 'TC00000003',
//             'firstname' => 'Jane',
//             'lastname' => 'Doe',
//             'email' => 'tutor2@tutorialcenter.com',
//             'phone' => '08020000003',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Female',
//             'staff_role' => 'tutor',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // Create an adviser 1
//         Staff::insert([
//             'staff_id' => 'TC00000004',
//             'firstname' => 'Oluwaseun',
//             'lastname' => 'Okechukwu',
//             'email' => 'adviser1@tutorialcenter.com',
//             'phone' => '08020000004',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Male',
//             'staff_role' => 'adviser',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // Create an adviser 2
//         Staff::insert([
//             'staff_id' => 'TC00000005',
//             'firstname' => 'Aishat',
//             'lastname' => 'Olanrewaju',
//             'email' => 'adviser2@tutorialcenter.com',
//             'phone' => '08020000005',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Female',
//             'staff_role' => 'adviser',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // Create an staff 1
//         Staff::insert([
//             'staff_id' => 'TC00000006',
//             'firstname' => 'Chinedu',
//             'lastname' => 'Olawumi',
//             'email' => 'staff1@tutorialcenter.com',
//             'phone' => '08020000006',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Male',
//             'staff_role' => 'staff',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // Create an staff 2
//         Staff::insert([
//             'staff_id' => 'TC00000007',
//             'firstname' => 'Noimot',
//             'lastname' => 'Nwosu',
//             'email' => 'staff2@tutorialcenter.com',
//             'phone' => '08020000007',
//             'password' => Hash::make('Qwertyuiop@1'),
//             'gender' => 'Female',
//             'staff_role' => 'staff',
//             'date_of_birth' => now(),
//             'email_verified_at' => now(),
//             'phone_verified_at' => now(),
//             'verification_code' => null,
//             'verified' => true,
//             'status' => 'active',
//             'home_address' => '1, Tutorial Center Building, Lagos',
//         ]);

//         // // Create an admin
//         // Staff::insert([
//         //     'staff_id' => 'TC00000001',
//         //     'firstname' => 'Olugbenga',
//         //     'lastname' => 'Raymond',
//         //     'email' => 'olugbengaraymond20@gmail.com',
//         //     'phone' => '08029606405',
//         //     'password' => Hash::make('Qwertyuiop@1'),
//         //     'gender' => 'Male',
//         //     'staff_role' => 'admin',
//         //     'date_of_birth' => now(),
//         //     'email_verified_at' => now(),
//         //     'phone_verified_at' => now(),
//         //     'verification_code' => null,
//         //     'verified' => true,
//         //     'status' => 'active',
//         //     'home_address' => '1, Tutorial Center Building, Lagos',
            
//         // ]);

//         // // Generate 10 random staff
//         // Staff::factory()->count(10)->create();
//     }
// }
