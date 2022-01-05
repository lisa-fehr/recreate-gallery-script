<?php

namespace App\Http\Controllers;

use App\Models\UberGallery;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GalleryController extends Controller
{
    public function __invoke()
    {
        $paginator = QueryBuilder::for(UberGallery::class)
            ->allowedFilters([
                AllowedFilter::scope('tags')
            ])
            ->whereHas('tag')
            ->whereNotNull('thumb')
            ->orderBy('thumb', 'desc')
            ->paginate(12);

        return response()->json($paginator);
    }
}
