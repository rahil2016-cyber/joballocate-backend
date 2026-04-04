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

/*
| Job seeker resumes (PDF): stream from storage/app/public/resumes — no symlink required.
*/
Route::get('/media/resumes/{file}', function (string $file) {
    $file = basename($file);
    if ($file === '' || ! preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
        abort(404);
    }

    $path = 'resumes/'.$file;
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path, null, [
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('file', '[a-zA-Z0-9._-]+');

/*
| Job seeker profile photos & company logos: same 403 issue as banner-ads when /storage is blocked.
| Stream from storage/app/public/profile-photos and company-logos.
*/
Route::get('/media/profile-photos/{file}', function (string $file) {
    $file = basename($file);
    if ($file === '' || ! preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
        abort(404);
    }

    $path = 'profile-photos/'.$file;
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path, null, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('file', '[a-zA-Z0-9._-]+');

Route::get('/media/company-logos/{file}', function (string $file) {
    $file = basename($file);
    if ($file === '' || ! preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
        abort(404);
    }

    $path = 'company-logos/'.$file;
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path, null, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('file', '[a-zA-Z0-9._-]+');
