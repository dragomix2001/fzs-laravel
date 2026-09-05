<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'fzs@fzs.rs')->first();

        if (! $user) {
            User::create([
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
            TestDataSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
