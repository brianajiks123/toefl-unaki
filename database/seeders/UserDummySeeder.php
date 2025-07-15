<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{
    DB,
    Hash,
};
use Illuminate\Support\Str;

class UserDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Admin TOEFL UNAKI | Testing',
            'email' => 'admin@unaki.ac.id',
            'email_verified_at' => now(),
            'is_admin' => 1,
            'password' => Hash::make(12345678),
            'remember_token' => Str::random(10)
        ]);

        DB::table('users')->insert([
            'name' => 'User Testing',
            'email' => 'testing@student.unaki.ac.id',
            'email_verified_at' => now(),
            'is_admin' => 0,
            'password' => Hash::make(12345678),
            'remember_token' => Str::random(10)
        ]);
    }
}
