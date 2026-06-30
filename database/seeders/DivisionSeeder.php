<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            'Management',
            'Sektor 1',
            'Sektor 2',
            'Sektor 3',
            'Sektor 4',
            'Sektor 5',
            'Sektor 6',
            'Sektor 7',
            'Sektor 8',
            'Sektor 9',
            'Sektor 10',
        ];

        foreach ($divisions as $division) {
            Division::firstOrCreate([
                'name' => $division,
            ]);
        }
    }
}
