<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class RandomImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $path = Storage::disk('gallery')->path('random');

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        $coverage = [
            'digital',
            'sketch',
            'unsorted',
        ];

        // create empty canvas with background color
        $img = Image::canvas(125, 175, '#ddd');

        $points = [
            rand(40, 50),
            rand(50, 60),  // Point 1 (x, y)
            rand(10, 20),
            rand(90, 170), // Point 2 (x, y)
            rand(50, 60),
            rand(50, 60),  // Point 3 (x, y)
            rand(90, 170),
            rand(10, 20),  // Point 4 (x, y)
            rand(50, 60),
            rand(40, 50),  // Point 5 (x, y)
            rand(0, 10),
            rand(0, 10)   // Point 6 (x, y)
        ];

        // draw filled red rectangle
        $img->polygon($points, function ($draw) {

            $draw->background('#ffffff');
        });

        $name = "random".Str::random(30).".png";
        $img->save(Storage::disk('gallery')->path('random/'.$name));
    }
}
