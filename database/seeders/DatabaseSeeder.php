<?php

namespace Database\Seeders;

use App\Models\auteur;
use App\Models\User;
use App\Models\livre;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        //livre::factory(10)->create();
        auteur::factory(10)->create();

    }
}
