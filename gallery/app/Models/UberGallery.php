<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UberGallery extends Model
{

    use HasFactory;

    protected $table = 'uber_gallery';
    public $timestamps = false;

    public $fillable = [
        'occurred',
        'img',
        'thumb',
        'type',
        'text',
    ];

    public function tag() {

        return $this->hasOneThrough(
            UberTags::class,
            UberTagAssoc::class,
            'image_id', // Foreign key on the UberTagAssoc...
            'id', // Foreign key on the UberTags...
            'id', // Local key on the UberGallery...
            'tag_id' // Local key on the UberTagAssoc...
        );
    }

    public function getThumbnailAttribute() {
        return Storage::disk('gallery')->url($this->tag->directory . '/t/' . $this->thumb);
    }
}
