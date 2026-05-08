<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Admin SPA (static build) entrypoint.
 *
 * Deploy the admin web build output into: public/admin/
 * - public/admin/index.html
 * - public/admin/assets/...
 *
 * This route makes sure refreshing deep links like /admin/visa still works.
 */
Route::get('/admin/{any?}', function (string $any = ''): Response {
    $indexPath = public_path('admin/index.html');
    abort_unless(File::exists($indexPath), 404, 'Admin app not deployed.');

    return response(File::get($indexPath), 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
        // Avoid aggressive caching during rollout; let the SPA assets handle hashing.
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
})->where('any', '.*');
