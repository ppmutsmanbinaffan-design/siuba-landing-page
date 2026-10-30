<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes - SIUBA Absensi Santri SPA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $landingPath = public_path('landing.html');
    if (File::exists($landingPath)) {
        return response()->file($landingPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
    return redirect('/login');
});

Route::get('/{any}', function () {
    $indexPath = public_path('app.html');
    if (!File::exists($indexPath)) {
        $indexPath = base_path('dist/index.html');
    }
    
    if (File::exists($indexPath)) {
        return response()->file($indexPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
    
    return response()->json([
        'name' => 'SIUBA Absensi Santri Backend API',
        'status' => 'online',
        'message' => 'Frontend build file not found. Please run `npm run build`'
    ]);
})->where('any', '^(?!api).*$');

