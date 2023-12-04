<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;
use DB;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Division::truncate();
        $path   = base_path('public/sql/divisions.sql');
        $sql    = file_get_contents($path);
        DB::unprepared($sql);
    }
}
