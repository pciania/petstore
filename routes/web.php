<?php

use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('pets.index'));

Route::resource('pets', PetController::class);

Route::get('pets/{pet}/upload', [PetController::class, 'uploadForm'])->name('pets.upload.form');
Route::post('pets/{pet}/upload', [PetController::class, 'uploadImage'])->name('pets.upload');

