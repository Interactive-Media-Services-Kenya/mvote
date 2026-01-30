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
        $genres = ['Afro-Pop', 'Afro-Fusion', 'Afrobeats', 'Alt-R&B', 'Bongo Flava', 'Reggae', 'Hip Hop', 'Dancehall'];
        
        foreach ($genres as $genre) {
            \App\Models\Genre::firstOrCreate(
                ['title' => $genre],
                ['description' => $genre . ' musical genre']
            );
        }
    }
}
