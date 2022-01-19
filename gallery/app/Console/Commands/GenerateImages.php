<?php

namespace App\Console\Commands;

use App\Models\UberGallery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class GenerateImages extends Command
{
    const THUMBNAIL_WIDTH = 125;
    const THUMBNAIL_HEIGHT = 175;
    const THUMBNAIL_QUALITY = 75;

    const MAX_IMAGE_WIDTH = 800;
    const MAX_IMAGE_HEIGHT = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate a gallery from a folder and a database';

    private $imageDestination = null;
    private $thumbnailDestination = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $originalImages = Storage::disk("gallery-originals")->allFiles();

        foreach ($originalImages as $originalImage) {
            $path = pathinfo($originalImage, PATHINFO_DIRNAME);
            $this->imageDestination = Storage::disk('gallery')->path($path);
            $this->thumbnailDestination = $this->imageDestination . '/t';

            if (! File::exists($this->thumbnailDestination)) {
                $this->info('Created directories for: ' . $path);

                File::makeDirectory($this->thumbnailDestination, 0755, true, true);
            }

            $this->resizeImage($originalImage);
        }

        $this->createMissingThumbnail();

        return 0;
    }

    /**
     * @param $originalImage
     */
    private function resizeImage($originalImage)
    {
        $originalPath = Storage::disk('gallery-originals')->path($originalImage);

        $name = pathinfo($originalImage, PATHINFO_FILENAME);
        $image = UberGallery::firstWhere('img', $name);

        if ($image) {
            $thumbPath = $this->thumbnailDestination . '/' . $image->thumb;
            $imagePath = $this->imageDestination . '/' . $image->img . '.' . $image->type;

            Image::make($originalPath)
                ->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT)
                ->sharpen(5)
                ->save($thumbPath, self::THUMBNAIL_QUALITY, pathinfo($image->thumb, PATHINFO_EXTENSION));

            $modalImage = Image::make($originalPath);
            list($width, $height) = $this->calculateWidthHeight($modalImage);
            $modalImage->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            })->save($imagePath);

            $this->info('Created an image and thumbnail: ' . $originalImage);
        } else {
            $this->info('Could not find: ' . $originalImage);
        }
    }

    /**
     * @param \Intervention\Image\Image $modalImage
     * @return array
     */
    private function calculateWidthHeight(\Intervention\Image\Image $modalImage)
    {
        $width = self::MAX_IMAGE_WIDTH;
        $height = self::MAX_IMAGE_HEIGHT;
        if ($modalImage->height() < $height || $modalImage->width() < $width) {
            $width = $modalImage->width();
            $height = $modalImage->height();
        } elseif ($modalImage->height() > $modalImage->width()) {
            $width = null;
        } else {
            $height = null;
        }
        return [$width, $height];
    }

    private function createMissingThumbnail()
    {
        $img = Image::canvas(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, '#ffa500');
        $img
        ->line(0, 0, self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, function ($draw) {
            $draw->color('#ffb52e');
        })
        ->line(self::THUMBNAIL_WIDTH, 0, 0, self::THUMBNAIL_HEIGHT, function ($draw) {
            $draw->color('#ffb52e');
        })
        ->text('Missing Image', 28, 20, function ($font) {
            $font->color('#000');
        });
        $img->save(Storage::disk('gallery')->path('missing.gif'), 90, 'gif');
        $this->info('Created missing image: ' . Storage::disk('gallery')->path('missing.gif'));
    }
}
