<?php

use App\Http\Controllers\API\ImportsController;
use App\Http\Controllers\API\PropertiesController;
use Illuminate\Support\Facades\Route;

Route::post('/imports', [ImportsController::class, 'load'])->name('imports.load');
Route::get('/imports/{import}', [ImportsController::class, 'getStatus'])->name('imports.status');
Route::get('/properties', [PropertiesController::class, 'index'])->name('properties.index');
