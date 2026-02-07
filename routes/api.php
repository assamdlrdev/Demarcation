<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('store-application', [App\Http\Controllers\Api\CitizenApplications::class, 'store']);
Route::post('get-application-details', [App\Http\Controllers\Api\CitizenApplications::class, 'getApplicatonDetails']);
Route::post('edit-application', [App\Http\Controllers\Api\CitizenApplications::class, 'editApplication']);
Route::post('final-submit-application', [App\Http\Controllers\Api\CitizenApplications::class, 'finalSubmitApplication']);

// Route::middleware('jwt')->group(function () {
Route::post('get-districts', [App\Http\Controllers\Api\LocationController::class, 'getDistricts']);
// Route::get('insert-test', [App\Http\Controllers\Api\LocationController::class, 'insertTest']);
Route::post('get-subdivs', [App\Http\Controllers\Api\LocationController::class, 'getSubdivs']);
Route::post('get-circles', [App\Http\Controllers\Api\LocationController::class, 'getCircles']);
Route::post('get-mouzas', [App\Http\Controllers\Api\LocationController::class, 'getMouzas']);
Route::post('get-lots', [App\Http\Controllers\Api\LocationController::class, 'getLots']);
Route::post('get-vills', [App\Http\Controllers\Api\LocationController::class, 'getVills']);
Route::post('get-pattatypes-landclasses', [App\Http\Controllers\Api\LocationController::class, 'getPattaTypesLandClasses']);
Route::post('get-pattanos', [App\Http\Controllers\Api\LocationController::class, 'getPattaNos']);
Route::post('get-dags', [App\Http\Controllers\Api\LocationController::class, 'getDags']);
Route::post('get-pattadar-list', [App\Http\Controllers\Api\LocationController::class, 'getPattadarList']);
Route::post('get-ekhajana-receipt-number', [App\Http\Controllers\Api\LocationController::class, 'getEkhajanaReceiptNumber']);
Route::post('get-ekhajana', [App\Http\Controllers\Api\LocationController::class, 'getKhajana']);

// });

