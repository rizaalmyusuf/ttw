<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Classroom;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('Admin'),
            'name' => 'Administrator',
            'email' => 'admin@ttw.id',
            'role' => 0,
        ]);

        Classroom::factory(15)
            ->recycle(
                User::factory()->create([
                    'username' => 'gurutik',
                    'password' => bcrypt('GuruTIK'),
                    'name' => 'Guru TIK',
                    'email' => 'gurutik@ttw.id',
                    'role' => 1,
                ]),
                User::factory(2)->create([
                    'role' => 1,
                ]),
                User::factory()->create([
                    'username' => 'muridtik',
                    'password' => bcrypt('MuridTIK'),
                    'name' => 'Murid TIK',
                    'email' => 'muridtik@ttw.id',
                    'role' => 2,
                ]),
                User::factory(10)->create([
                    'role' => 2,
                ]),
            )
            ->create();
    }
}
