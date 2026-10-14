<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        User::firstOrCreate(
            ['email' => 'admin@berbagibaik.test'],
            [
                'name' => 'Admin BerbagiBaik',
                'password' => 'admin12345',
                'role' => 'admin',
                'phone_wa' => null,
            ]
        );
    }
}
