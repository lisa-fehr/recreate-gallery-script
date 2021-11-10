<?php

namespace Database\Seeders;

use App\Models\UberTags;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UberTags::truncate();

        $parentFilters = [
            ['all', '', '', 420],
            ['photos', '', 'Photos', 148],
        ];

        collect($parentFilters)->each(function ($item) {
            UberTags::create([
                'name' => $item[0],
                'display_name' => $item[1],
                'parent' => 0,
                'children' => true,
                'directory' => $item[2],
                'count' => $item[3],
            ]);
        });

        $photos = UberTags::where('name', 'photos')->first();
        $california = UberTags::create([
            'name' => 'California',
            'display_name' => '',
            'parent' => $photos->id,
            'children' => true,
            'directory' => '',
            'count' => 109,
        ]);

        UberTags::create([
            'name' => 'California2014',
            'display_name' => '2014',
            'parent' => $california->id,
            'children' => false,
            'directory' => 'Photos/California/2014',
            'count' => 33,
        ]);
    }
}
