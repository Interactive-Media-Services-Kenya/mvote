<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $artists = [
            [
                'name' => 'Sauti Sol',
                'genre' => 'Afro-Pop',
                'status' => 'live',
                'bio' => 'Sauti Sol is an award-winning afro-pop group from Nairobi, Kenya.',
                'photo' => 'https://api.dicebear.com/7.x/initials/svg?seed=Sauti%20Sol',
            ],
            [
                'name' => 'Burna Boy',
                'genre' => 'Afro-Fusion',
                'status' => 'upcoming',
                'bio' => 'The African Giant.',
                'photo' => 'https://api.dicebear.com/7.x/initials/svg?seed=Burna%20Boy',
            ],
            [
                'name' => 'Wizkid',
                'genre' => 'Afrobeats',
                'status' => 'upcoming',
                'bio' => 'Starboy. Wizkid is one of the most influential African artists of all time.',
                'photo' => 'https://api.dicebear.com/7.x/initials/svg?seed=Wizkid',
            ],
            [
                'name' => 'Tems',
                'genre' => 'Alt-R&B',
                'status' => 'closed',
                'bio' => 'Tems is a Nigerian singer and songwriter.',
                'photo' => 'https://api.dicebear.com/7.x/initials/svg?seed=Tems',
            ],
            [
                'name' => 'Diamond Platnumz',
                'genre' => 'Bongo Flava',
                'status' => 'closed',
                'bio' => 'The King of Bongo Flava.',
                'photo' => 'https://api.dicebear.com/7.x/initials/svg?seed=Diamond%20Platnumz',
            ],
            [
                'name' => 'Nesta',
                'genre' => 'Reggae',
                'status' => 'upcoming',
                'bio' => 'A rising star in the contemporary reggae scene.',
                'photo' => 'https://api.dicebear.com/7.x/initials/svg?seed=Nesta',
            ],
        ];

        foreach ($artists as $a) {
            $genre = \App\Models\Genre::where('title', $a['genre'])->first();
            \App\Models\Artist::updateOrCreate(
                ['name' => $a['name']],
                [
                    'genre_id' => $genre->id ?? 1,
                    'status' => $a['status'],
                    'bio' => $a['bio'],
                    'photo' => $a['photo'],
                    'is_active' => true,
                ]
            );
        }
    }
}
