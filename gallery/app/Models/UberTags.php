<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UberTags extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'name',
        'display_name',
        'parent',
        'children',
        'directory',
        'count',
        'details',
    ];
}
