<?php

namespace App\Console\Commands;

use App\Models\UberGallery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class GenerateImages extends Command
{
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

            Image::make($originalPath)->resize(125, 175)->sharpen(5)->save($thumbPath, 75, pathinfo($image->thumb, PATHINFO_EXTENSION));

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
        $width = 800;
        $height = 1000;
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
}
