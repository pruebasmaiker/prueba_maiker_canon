<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'username' => 'usuario1',
                'email' => 'usuario1@comfamiliarhuila.com',
                'password' => bcrypt('password1'),
            ],
            [
                'username' => 'usuario2',
                'email' => 'usuario2@comfamiliarhuila.com',
                'password' => bcrypt('password2'),
            ],
            [
                'username' => 'usuario3',
                'email' => 'usuario3@comfamiliarhuila.com',
                'password' => bcrypt('password3'),
            ],
            [
                'username' => 'usuario4',
                'email' => 'usuario4@comfamiliarhuila.com',
                'password' => bcrypt('password4'),
            ],
            [
                'username' => 'usuario5',
                'email' => 'usuario5@comfamiliarhuila.com',
                'password' => bcrypt('password5'),
            ],
        ]);
    }
}
