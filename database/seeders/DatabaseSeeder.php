<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = \App\Models\User::where('email', 'fzs@fzs.rs')->first();

        if (! $user) {
            \App\Models\User::create([
                'name' => 'FZS Admin',
                'email' => 'fzs@fzs.rs',
                'password' => Hash::make('fzs123'),
                'role' => 'admin',
            ]);
            $this->command->info('Test user created: fzs@fzs.rs / fzs123');
        } else {
            $user->update(['password' => Hash::make('fzs123')]);
            $this->command->info('Test user password updated: fzs@fzs.rs / fzs123');
        }

        $this->call([
            StatusGodineTableSeeder::class,
        ]);

        // Note: TestDataSeeder is not called here because it requires
        // additional database columns that may not exist in all environments
        // Run it manually if needed: php artisan db:seed --class=TestDataSeeder

        $this->command->info('Database seeded successfully!');
    }
}
