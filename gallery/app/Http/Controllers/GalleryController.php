<?php

namespace App\Http\Controllers;

use App\Models\UberGallery;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GalleryController extends Controller
{
    public function __invoke()
    {
        $images = QueryBuilder::for(UberGallery::class)
            ->allowedFilters([
                AllowedFilter::scope('tags')
            ])
            ->get()
            ->reject(function (UberGallery $image) {

                return empty($image->thumbnail);
            })
            ->map(function (UberGallery $image) {
                return [
                    'image' => $image->image,
                    'thumbnail' => $image->thumbnail,
                ];
            });

        return response()->json($images);
    }
}
