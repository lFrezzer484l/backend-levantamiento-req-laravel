<?php

use App\Http\Controllers\RequirementController;
use App\Http\Controllers\GeminiController;
use Illuminate\Support\Facades\Route;

// CRUD de requerimientos
Route::get('/requirements', [RequirementController::class, 'index']);
Route::post('/requirements/create', [RequirementController::class, 'store']);
Route::get('/requirements/{id}', [RequirementController::class, 'show']);
Route::put('/requirements/{id}', [RequirementController::class, 'update']);
Route::delete('/requirements/{id}', [RequirementController::class, 'destroy']);

// Inteligencia artificial
Route::post('/ai/ask', [GeminiController::class, 'ask']);