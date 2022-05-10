<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploader;

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

Route::get('/t', function () {
    dispatch(new App\Jobs\CsvFormaterJob('asdas'));
    dd('dasdas');
});

Route::get('/', [FileUploader::class, 'index']);
Route::post('/upload', [FileUploader::class, 'uploadToServer']);