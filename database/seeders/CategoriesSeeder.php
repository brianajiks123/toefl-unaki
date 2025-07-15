<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            'name' => 'Listening'
        ]);

        DB::table('categories')->insert([
            'name' => 'Structure'
        ]);

        DB::table('categories')->insert([
            'name' => 'Reading'
        ]);
    }
}
