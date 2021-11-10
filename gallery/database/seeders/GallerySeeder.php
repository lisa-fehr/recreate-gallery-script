<?php

namespace Database\Seeders;

use App\Models\UberGallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UberGallery::truncate();

        $images = [
            ['2014-10-04', '141004', 'JapanLA_1'],
            ['2014-10-04', '141004', 'JapanLA_2'],
            ['2014-10-04', '141004', 'JapanLA_3'],
            ['2014-10-04', '141004', 'JapanLA_4'],
            ['2014-10-10', '141010', 'Aquarium_1'],
            ['2014-10-10', '141010', 'Aquarium_2'],
            ['2014-10-10', '141010', 'Aquarium_3'],
            ['2014-10-10', '141010', 'Aquarium_4'],
            ['2014-10-10', '141010', 'Aquarium_5'],
            ['2014-10-10', '141010', 'Aquarium_7'],
            ['2014-10-10', '141010', 'Aquarium_8'],
        ];

        collect($images)->each(function ($item) {
            UberGallery::create([
                'occurred' => $item[0],
                'img' => $item[2],
                'thumb' => $item[1] . $item[2] . '.gif',
                'type' => 'jpg',
            ]);
        });
    }
}
