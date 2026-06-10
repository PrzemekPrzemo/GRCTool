<?php

use App\Http\Controllers\CorrectiveActionPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'mfa'])->group(function (): void {
    // CAP resource (index, create, store, show, edit, update)
    Route::resource('cap', CorrectiveActionPlanController::class)
        ->except(['destroy']);

    // Zatwierdzenie planu
    Route::post('cap/{cap}/approve', [CorrectiveActionPlanController::class, 'approve'])
        ->name('cap.approve');

    // Dodanie akcji naprawczej do planu
    Route::post('cap/{cap}/action', [CorrectiveActionPlanController::class, 'addAction'])
        ->name('cap.action');

    // Aktualizacja akcji naprawczej (inline)
    Route::patch('cap-actions/{action}', [CorrectiveActionPlanController::class, 'updateAction'])
        ->name('cap-actions.update');
});
