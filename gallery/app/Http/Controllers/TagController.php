<?php

namespace App\Http\Controllers;

use App\Models\UberTags;

class TagController extends Controller
{
    public function __invoke($filters = '')
    {
        if (empty($filters)) {
            return response()->json([
                'children' =>
                    UberTags::where('parent', 0)
                    ->get()
            ]);
        }

        $names = explode(',', $filters);

        return response()->json([
            'current' => UberTags::where('name', $names[0])
                ->with('parent')
                ->first(),
            'children' =>
                UberTags::whereIn('name', $names)
                    ->children()
                    ->with('parent')
                    ->orderBy('parent')
                    ->get()
        ]);
    }
}
