<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create an admin user for Fortify testing/login.
        if (! User::where('email', 'admin@admin.com')->exists()) {
            
            User::create([
                'name' => 'Fortify Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('pwd12345'),
                'email_verified_at' => now(),
            ]);

            // Optional: output confirmation to the console
            // Note: $this->command is only available if called directly by $this->call() or via CLI
            // $this->command->info('Admin user created successfully in UserSeeder.'); 
        }
    }
}