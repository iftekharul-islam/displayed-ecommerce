<?php

namespace Database\Seeders;

use App\Models\ShortUrlType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShortUrlTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShortUrlType::create([
            'name' => 'Default',
            'redirect_url' => 'https://google.com',
            'is_default' => true,
        ]);
    }
}
