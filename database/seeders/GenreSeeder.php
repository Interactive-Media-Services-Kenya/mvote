<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'gengetone',
            'afro pop',
            'afrosoul',
            '3 step',
            'jazz,rap',
            'kompa',
            'rumba congolaise',
            '-',
            'afro r&b',
            'afro soul'
        ];
        
        foreach ($genres as $genre) {
            \App\Models\Genre::firstOrCreate(
                ['title' => $genre],
                ['description' => $genre === '-' ? 'No genre specified' : $genre . ' musical genre']
            );
        }
    }
}
