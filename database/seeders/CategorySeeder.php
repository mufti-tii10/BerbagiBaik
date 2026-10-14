<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Pakaian',
            'Buku',
            'Mainan',
            'Perlengkapan Rumah Tangga',
            'Perlengkapan Sekolah',
            'Elektronik',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
