<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
            $this->resizeImage($image);
        }

        return 0;
    }

    private function resizeImage($image)
    {
        $destination = '/tmp';
        $path = Storage::disk('gallery-originals')->path($image);
        Image::make($path)->resize(200, 200)->save($destination .'/'.basename($image, 'jpg') . "-resized.jpg");
    }
}
