<?php

// Orgnizer routes
use App\Http\Controllers\Organizer\ContestController;
use App\Http\Controllers\Organizer\ParticipantController;
use App\Http\Controllers\WithdrawalController;

// Categories
Route::get('/categories', [ContestController::class, 'categories']);
// Contests
Route::prefix('contests')->controller(ContestController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    // Analytics and reports
    Route::get('/analytics', 'analytics');
    Route::get('/{id}/analytics', 'contestAnalytics');
});
Route::prefix('contests/{id}')->controller(ContestController::class)->group(function () {
    Route::get('/', 'show');
    Route::patch('/', 'update');
    Route::delete('/', 'destroy');
    Route::patch('/end', 'endContest');

    Route::post('/notification', 'sendNotification');
    Route::get('/participants', 'participants');
    Route::get('/submissions', 'submissions');
    Route::get('/leaderboards', 'leaderboards');
});
// submissions
Route::prefix('submissions')->controller(ParticipantController::class)->group(function () {
    Route::get('/{id}', 'showSubmission');
    Route::post('/{id}/approve', 'approveSubmission');
    Route::post('/{id}/reject', 'rejectSubmission');
    Route::delete('/{id}', 'deleteSubmission');
});

// Participant routes
Route::prefix('participants')->controller(ParticipantController::class)->group(function () {
    Route::get('/{id}', 'show');
    Route::patch('/{id}', 'update');
    Route::put('/status/{id}', 'updateStatus');
    Route::delete('/{id}', 'delete');
});
// Reports

//  Withdrawal Routes
Route::prefix('withdrawals')->controller(WithdrawalController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'create');
    Route::get('/details', 'details');
    Route::get('/{id}', 'show');
    Route::post('/{id}/cancel', 'cancel');
    Route::put('/settings', 'updateSettings');
});
