<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

/*
| Banner images: many hosts return 403 for /storage/* (permissions, nginx rules, missing symlink).
| This route streams files from storage/app/public/banner-ads — no symlink required.
*/
Route::get('/media/banner-ads/{file}', function (string $file) {
    $file = basename($file);
    if ($file === '' || ! preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
        abort(404);
    }

    $path = 'banner-ads/'.$file;
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path, null, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('file', '[a-zA-Z0-9._-]+');
