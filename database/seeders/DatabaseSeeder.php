<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('12345678'),
        ]);

        DB::table('admins')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => Hash::make('0000'),
                'created_at' => now(),
            ]
        ]);
        DB::table('companies')->insert([
            [
                'name' => 'Company User',
                'email' => 'company@company.com',
                'password' => Hash::make('0000'),
                'created_at' => now(),
            ]
        ]);
    }
}
