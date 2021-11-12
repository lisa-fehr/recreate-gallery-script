<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('portfolio')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('photos', function () {
        return view('welcome')->with('filter', ['tags' => 'photos,california2014']);
    })->name('photos');

    Route::get('events', function () {
        return view('welcome')->with('filter', ['tags' => 'events,california2014']);
    })->name('events');

    Route::get('California', function () {
        return view('welcome')->with('filter', ['tags' => 'california,california2014']);
    })->name('California');

    Route::get('California/2014', function () {
        return view('welcome')->with('filter', ['tags' => 'california2014']);
    })->name('California2014');
});

Route::get('/gallery', GalleryController::class);
// /gallery?filter[tags]=California2014
Route::get('/tags/{filters?}', TagController::class);
