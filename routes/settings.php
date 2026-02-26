<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Importamos los componentes
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\TwoFactor;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Usamos el método render o simplemente nos aseguramos que apunte correctamente
    Route::get('settings/profile', Profile::class)->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');
    Route::get('settings/two-factor', TwoFactor::class)->name('two-factor.show');
});