<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Quan tri vien',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach (['weak_threshold' => '50', 'average_threshold' => '70', 'good_threshold' => '85'] as $key => $value) {
            DB::table('system_settings')->insert([
                'key' => $key, 'value' => $value,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
