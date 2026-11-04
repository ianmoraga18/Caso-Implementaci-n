<?php

use App\Http\Controllers\CursoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/cursos', [CursoController::class, 'store'])->name('cursos.store');