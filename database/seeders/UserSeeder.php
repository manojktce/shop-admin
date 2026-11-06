<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::$superAdmin;
        $admin = User::$admin;
        $customer = User::$customer;

        $users = [
            [
                [
                    'name' => 'Super Admin',
                    'email' => 'superadmin@example.com',
                    'password' => '123456',
                    'gender' => '1',
                    'status' => '1'
                ],
                $superAdmin
            ],
            [
                [
                    'name' => 'Admin',
                    'email' => 'admin@example.com',
                    'password' => '123456',
                    'gender' => '1',
                    'status' => '1'
                ],
                $admin
            ],
            [
                [
                    'name' => 'Customer',
                    'email' => 'customer@example.com',
                    'password' => '123456',
                    'gender' => '1',
                    'status' => '1'
                ],
                $customer
            ]
        ];

        foreach ($users as $user) {
            $result = User::updateOrCreate(
                ['email' => $user[0]['email']],
                $user[0]
            );

            $result->assignRole($user[1]);
        }
    }
}
