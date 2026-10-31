<?php

use App\Http\Controllers\PhoneController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');

    // Member info page (storefront) + phone numbers (user_phones)
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('settings/phones', [PhoneController::class, 'store'])->name('phones.store');
    Route::delete('settings/phones/{phoneNo}', [PhoneController::class, 'destroy'])->name('phones.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->middleware([
            'password.confirm',
        ])
        ->name('security.edit');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
