<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('store-application', [App\Http\Controllers\Api\CitizenApplications::class, 'store']);
Route::get('get-districts', [App\Http\Controllers\Api\LocationController::class, 'getDistricts']);
