<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;
use DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        District::truncate();
        $path   = base_path('public/sql/districts.sql');
        $sql    = file_get_contents($path);
        DB::unprepared($sql);
    }
}
