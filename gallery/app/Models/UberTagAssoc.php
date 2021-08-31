<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UberTagAssoc extends Model
{
    use HasFactory;

    protected $table = 'uber_tag_assoc';
    public $timestamps = false;

    public $fillable = [
        'tag_id',
        'image_id',
    ];
}
