<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotesController;

Route::get('/notes', [NotesController::class, 'index']);
Route::post('/notes', [NotesController::class, 'store']);
Route::patch('/notes/{note}/archive', [NotesController::class, 'archive']);