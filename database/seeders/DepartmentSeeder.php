<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Add more later; keep minimal for now
        $names = [
            'ECOAST',
            'CCS',
            'COE',
        ];

        foreach ($names as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
