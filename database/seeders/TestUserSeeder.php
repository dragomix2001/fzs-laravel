<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TestUserSeeder - Creates a test user for Playwright E2E tests
 * 
 * Credentials:
 * - Email: test@fzs.test
 * - Password: password
 * - Role: admin
 * 
 * Usage:
 *   php artisan db:seed --class=TestUserSeeder
 */
class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if test user already exists
        $existingUser = User::where('email', 'test@fzs.test')->first();
        
        if ($existingUser) {
            $this->command->info('Test user already exists. Updating...');
            $existingUser->update([
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]);
        } else {
            User::create([
                'name' => 'Playwright Test User',
                'email' => 'test@fzs.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]);
            
            $this->command->info('Test user created successfully.');
        }
    }
}
