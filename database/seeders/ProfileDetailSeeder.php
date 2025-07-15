<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profile_details')->insert([
            'user_id' => 1,
            'address' => '',
            'phone' => null
        ]);
        DB::table('profile_details')->insert([
            'user_id' => 2,
            'address' => '',
            'phone' => null
        ]);
    }
}
