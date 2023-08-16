<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Constants\RolesConstant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'is_active' => true,
                'password' => 123456,
            ]
        ];

        foreach ($users as $item) {
            $user = User::create($item);
            if ($item['name'] == 'Admin') {
                $user->assignRole(RolesConstant::ADMIN);
            } else {
                $user->assignRole(RolesConstant::EMPLOYEE);
            }
        }
    }
}
