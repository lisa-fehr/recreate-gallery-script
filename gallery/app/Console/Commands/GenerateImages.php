<?php

namespace App\Console\Commands;

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
    protected $description = 'Generate a processed copy of a folder of images.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $everything = Storage::disk("gallery-originals")->allFiles();

        foreach($everything as $image) {

            $path = pathinfo($image, PATHINFO_DIRNAME);
            $directory = Storage::disk('gallery')->path($path);
            if (! File::exists($directory)) {
                $this->info('Created a directory: ' . $path);

                File::makeDirectory($directory, 0755, true, true);
            }

            $this->resizeImage($image);
        }

        return 0;
    }

    private function resizeImage($image)
    {
        $path = Storage::disk('gallery-originals')->path($image);
        $destination = Storage::disk('gallery')->path(
            preg_replace('/([.][gjpne]{3,4})$/', '-resized\1', $image)
        );

        Image::make($path)->resize(200, 200)->save($destination);
        $this->info('Created a thumbnail: ' . $image);
    }
}
