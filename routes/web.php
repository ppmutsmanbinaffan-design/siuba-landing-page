<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes - SIUBA Absensi Santri SPA
|--------------------------------------------------------------------------
*/

$serveLanding = function () {
    $landingPath = public_path('landing.html');
    if (File::exists($landingPath)) {
        return response()->file($landingPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
    return redirect('/login');
};

Route::get('/', $serveLanding);
Route::get('/beranda', $serveLanding);
Route::get('/landing', $serveLanding);

Route::get('/cms-landing', function () {
    $cmsPath = public_path('cms-landing.html');
    if (File::exists($cmsPath)) {
        return response()->file($cmsPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
    return redirect('/dashboard');
});

$servePsbAdmin = function () {
    $psbPath = public_path('manajemen-psb.html');
    if (!File::exists($psbPath)) {
        $psbPath = public_path('manajemen-ppdb.html');
    }
    if (File::exists($psbPath)) {
        return response()->file($psbPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
    return redirect('/dashboard');
};

Route::get('/manajemen-psb', $servePsbAdmin);
Route::get('/psb-admin', $servePsbAdmin);
Route::get('/manajemen-ppdb', $servePsbAdmin);
Route::get('/ppdb-admin', $servePsbAdmin);

$servePsbPublic = function () {
    $psbPublicPath = public_path('psb.html');
    if (!File::exists($psbPublicPath)) {
        $psbPublicPath = public_path('ppdb.html');
    }
    if (File::exists($psbPublicPath)) {
        return response()->file($psbPublicPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
    return redirect('/beranda#psb');
};

Route::get('/psb', $servePsbPublic);
Route::get('/ppdb', $servePsbPublic);

Route::get('/{any}', function () {
    $indexPath = public_path('app.html');
    if (!File::exists($indexPath)) {
        $indexPath = base_path('dist/index.html');
    }
    
    if (File::exists($indexPath)) {
        return response()->file($indexPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
    
    return response()->json([
        'name' => 'SIUBA Absensi Santri Backend API',
        'status' => 'online',
        'message' => 'Frontend build file not found. Please run `npm run build`'
    ]);
})->where('any', '^(?!api).*$');

