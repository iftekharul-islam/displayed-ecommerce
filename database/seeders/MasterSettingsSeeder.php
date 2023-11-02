<?php

namespace Database\Seeders;

use App\Models\MasterSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterSetting::create([
            'redirection_type' => 1,
        ]);
    }
}
