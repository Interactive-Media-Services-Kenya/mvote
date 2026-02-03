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
            ['name' => 'Mbithi', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb1b647d7054c1e66647e539ab'],
            ['name' => 'Keem', 'genre' => 'afro pop', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb6ac4e029fa03f22928e593e7'],
            ['name' => 'Nasibi', 'genre' => 'afrosoul', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb08b1b93408dc1fd62e104510'],
            ['name' => 'Teslah', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb8bd75a98fc2c9e0a9a2c7181'],
            ['name' => 'Muringi', 'genre' => '3 step', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb0e1347e4e5b62690da9b3842'],
            ['name' => 'tg.blk', 'genre' => 'jazz,rap', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb72c4779086ce9cbd35505eb1'],
            ['name' => 'Manasseh Shalom', 'genre' => 'afrosoul', 'photo' => 'https://i.scdn.co/image/ab67616d0000b2734126b05f546bb762ee9554b1'],
            ['name' => 'Sabi Wu', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5ebfde9b0fd74b865f830a30036'],
            ['name' => 'ZEMAN', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb79f88d8cc3edd37f115f908d'],
            ['name' => '4Mr Frank White', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb56e9b1ba1b1addc6bca416ea'],
            ['name' => 'Genes1s', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5ebd9a48a4f7f825efa208a16cd'],
            ['name' => 'Hildah Watiri', 'genre' => 'afrosoul', 'photo' => 'https://i.scdn.co/image/ab6761610000e5ebeb1b82b6b8ddb90923be6a3a'],
            ['name' => 'Korb$', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb306790fd07b455532f894086'],
            ['name' => 'Zawadi Mukami', 'genre' => 'afro soul', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb9429c0c0989c48b1fb639597'],
            ['name' => 'Maali', 'genre' => 'afro soul', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb7daf1736b479e29726c40b3c'],
            ['name' => 'Dope-I-Mean', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5ebe4c3c0473c9926740180998e'],
            ['name' => 'Alma Ras', 'genre' => 'gengetone', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb3983f4692f0b088323e1706c'],
            ['name' => 'Chris Barr', 'genre' => 'afrosoul', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb36deabca770d5d78ca039b69'],
            ['name' => 'Miss Kamweru', 'genre' => 'kompa', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb61b7f58b62067f6e3d3f78e5'],
            ['name' => 'Apesi', 'genre' => 'rumba congolaise', 'photo' => 'https://i.scdn.co/image/ab6761610000e5ebbb96099cfae80864e8626eca'],
            ['name' => 'Njuguna', 'genre' => '-', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb7bb03f49f4448d7158d655f7'],
            ['name' => 'Kahuti', 'genre' => 'afro r&b', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb5349b3c1532669f224d73a16'],
            ['name' => 'Lafayette Pwaguzi', 'genre' => '-', 'photo' => 'https://i.scdn.co/image/ab67616d0000b273584b5d121ef85df0926c0700'],
            ['name' => '2wentysixx', 'genre' => 'afro soul', 'photo' => 'https://i.scdn.co/image/ab6761610000e5eb0b03e080a938020964346d23'],
        ];

        foreach ($artists as $a) {
            $genre = \App\Models\Genre::where('title', $a['genre'])->first();
            \App\Models\Artist::updateOrCreate(
                ['name' => $a['name']],
                [
                    'genre_id' => $genre->id ?? 1,
                    'status' => 'upcoming',
                    'bio' => $a['name'] . ' ' . $a['genre'],
                    'photo' => $a['photo'],
                    'is_active' => true,
                ]
            );
        }
    }
}
