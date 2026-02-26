<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CardController;

Route::view('/', 'welcome')->name('home');

Route::get('/cards', [CardController::class, 'index'])->name('cards.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

//require __DIR__.'/settings.php';
