<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function tag()
    {
        return $this->hasOneThrough(
            UberTags::class,
            UberTagAssoc::class,
            'image_id', // Foreign key on the UberTagAssoc...
            'id', // Foreign key on the UberTags...
            'id', // Local key on the UberGallery...
            'tag_id' // Local key on the UberTagAssoc...
        );
    }

    public function getImageAttribute()
    {
        $url = $this->tag->directory . '/' . $this->img . '.' . $this->type;
        if (Storage::disk('gallery')->exists($url)) {
            return Storage::disk('gallery')->url($url);
        }
        return Storage::disk('gallery')->url('/missing.gif');
    }

    public function getThumbnailAttribute()
    {
        if (! $this->tag) {
            return null;
        }
        $url = $this->tag->directory . '/t/' . $this->thumb;
        if (Storage::disk('gallery')->exists($url)) {
            return Storage::disk('gallery')->url($url);
        }
        return Storage::disk('gallery')->url('/missing.gif');
    }

    public function scopeTags(Builder $builder, ...$tags)
    {
        $builder
            ->select('uber_gallery.*')
            ->join('uber_tag_assoc', 'uber_gallery.id', '=', 'uber_tag_assoc.image_id')
            ->join('uber_tags', 'uber_tag_assoc.tag_id', '=', 'uber_tags.id')
            ->whereIn('uber_tags.name', $tags);
    }

    public function toArray()
    {
        return [
            'image' => $this->image,
            'thumbnail' => $this->thumbnail,
        ];
    }
}
