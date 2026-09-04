<?php

use App\Http\Controllers\API\ImportsController;
use Illuminate\Support\Facades\Route;

Route::post('/imports', [ImportsController::class, 'load'])->name('imports.load');
