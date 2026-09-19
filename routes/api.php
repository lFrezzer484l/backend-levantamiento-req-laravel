<?php

use App\Http\Controllers\RequirementController;
use Illuminate\Support\Facades\Route;

Route::get('/requirements', [RequirementController::class, 'index']);
Route::post('/requirements/create', [RequirementController::class, 'store']);
Route::get('/requirements/{id}', [RequirementController::class, 'show']);