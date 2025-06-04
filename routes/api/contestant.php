<?php

// Contestants Routes
use App\Http\Controllers\Contestant\ContestController;
use App\Http\Controllers\Contestant\ProfileController;

// Contests
Route::prefix('contests')->controller(ContestController::class)->group(function () {
    Route::post('/{id}/enter', 'enterContest');
});
// Manage Entries
Route::prefix('entries')->controller(ContestController::class)->group(function () {
    Route::get('/', 'contestEntries');
    Route::get('/active', 'activeEntries');
    Route::get('/{id}', 'showEntry');
    Route::patch('/{id}', 'updateEntry');
    Route::delete('/{id}', 'deleteEntry');
});
// Analytics
Route::get('/analytics/performance', [ProfileController::class, 'performance']);
