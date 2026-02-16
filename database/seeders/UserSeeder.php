<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Editor',
                'email' => 'editor@mafatihuljinan.org',
                'password' => 'editor@mafatihuljinan.org',
                'role' => 'editor',
            ],
            [
                'name' => 'Admin One',
                'email' => 'admin1@mafatihuljinan.org',
                'password' => 'admin1@mafatihuljinan.org',
                'role' => 'admin',
            ],
            [
                'name' => 'Admin Two',
                'email' => 'admin2@mafatihuljinan.org',
                'password' => 'admin2@mafatihuljinan.org',
                'role' => 'admin',
            ],
            [
                'name' => 'Manish Kumar',
                'email' => 'manishkumar@ibarts.in',
                'password' => 'manishkumar@ibarts.in',
                'role' => 'admin',
            ]
        ];
        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                    'role' => $data['role'],
                ]
            );
            if ($user->wasRecentlyCreated) {
                $this->command->info('User created successfully!');
            } else {
                $this->command->info('User updated successfully!');
            }
        }
    }
}
