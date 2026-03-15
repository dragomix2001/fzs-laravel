<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = \App\Models\User::where('email', 'fzs@fzs.rs')->first();
        
        if (!$user) {
            \App\Models\User::create([
                'name' => 'FZS Admin',
                'email' => 'fzs@fzs.rs',
                'password' => Hash::make('fzs123'),
            ]);
            $this->command->info('Test user created: fzs@fzs.rs / fzs123');
        } else {
            $user->update(['password' => Hash::make('fzs123')]);
            $this->command->info('Test user password updated: fzs@fzs.rs / fzs123');
        }
        
        if (class_exists(\Database\Seeders\UserSeeder::class)) {
            $this->call([
                UserSeeder::class,
            ]);
        }
        
        if (class_exists(\Database\Seeders\TestDataSeeder::class)) {
            $this->call([
                TestDataSeeder::class,
            ]);
        }
        
        $this->command->info('Database seeded successfully!');
    }
}
