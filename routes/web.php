<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::get('/phpinfo', function() {
    phpinfo();
});
Route::get('/upload-pdf', [App\Http\Controllers\Api\esign\AadhaarSignController::class, 'showUploadForm'])->name('esign.upload_form');
Route::post('/upload-store', [App\Http\Controllers\Api\esign\AadhaarSignController::class, 'uploadAndSignPdf'])->name('esign.upload');
// Route::any('/esign-response', [App\Http\Controllers\Api\esign\AadhaarSignController::class, 'esignResponse']);
// Add this at the TOP of web.php before any middleware
Route::post('/esign-process', [App\Http\Controllers\Api\esign\AadhaarSignController::class, 'esignProcess'])->name('esign.process');
Route::post('/esign-response', [App\Http\Controllers\Api\esign\AadhaarSignController::class, 'esignResponse']);


require __DIR__.'/settings.php';
