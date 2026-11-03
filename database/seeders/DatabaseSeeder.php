<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        // Data contoh hanya dibuat di laptop pengembang, bukan di server sungguhan
        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }

        User::firstOrCreate(
            ['email' => 'admin@berbagibaik.test'],
            [
                'name' => 'Admin BerbagiBaik',
                'password' => env('ADMIN_PASSWORD') ?: 'admin12345',
                'role' => 'admin',
                'phone_wa' => null,
            ]
        );
    }
}
