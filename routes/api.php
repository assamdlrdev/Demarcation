<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('store-application', [App\Http\Controllers\Api\CitizenApplications::class, 'store']);


Route::middleware('jwt')->group(function () {
    Route::post('get-districts', [App\Http\Controllers\Api\LocationController::class, 'getDistricts']);
    // Route::get('insert-test', [App\Http\Controllers\Api\LocationController::class, 'insertTest']);
    Route::post('get-subdivs', [App\Http\Controllers\Api\LocationController::class, 'getSubdivs']);
    Route::post('get-circles', [App\Http\Controllers\Api\LocationController::class, 'getCircles']);
    Route::post('get-mouzas', [App\Http\Controllers\Api\LocationController::class, 'getMouzas']);
    Route::post('get-lots', [App\Http\Controllers\Api\LocationController::class, 'getLots']);
    Route::post('get-vills', [App\Http\Controllers\Api\LocationController::class, 'getVills']);
});

