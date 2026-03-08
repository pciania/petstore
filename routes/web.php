<?php

use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('pets.index'));

Route::resource('pets', PetController::class);
