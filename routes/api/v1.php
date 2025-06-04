<?php

use App\Http\Controllers\CoinController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VotingController;
use Illuminate\Support\Facades\Route;

// Organizers routes
Route::prefix('organizer')->middleware(['auth:sanctum', 'role:organizer'])->group(function () {
    require __DIR__ . '/organizer.php';
});

// contestants routes
Route::prefix('contestant')->middleware(['auth:sanctum', 'role:contestant'])->group(function () {
    require __DIR__ . '/contestant.php';
});

// voters routes
Route::prefix('voter')->middleware(['auth:sanctum', 'role:voter'])->group(function () {
    require __DIR__ . '/voter.php';
});

// User Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Profile management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Community Engagement
    Route::prefix('community')->controller(CommunityController::class)->group(function () {
        // Posts
        Route::get('/posts', 'listPosts');
        Route::post('/posts', 'createPost');
        Route::put('/posts/{id}', 'updatePost');
        Route::delete('/posts/{id}', 'deletePost');
        Route::post('/posts/{id}/comments', 'commentOnPost');
        Route::delete('/comments/{id}', 'deleteComment');

        // Feed
        Route::get('/feed', 'feed');
        Route::post('/feed/{id}/like', 'like');
        Route::post('/feed/{id}/comment', 'comment');
    });
    // Notifications
    Route::get('/notifications', [ProfileController::class, 'notifications']);
    Route::put('/notifications/read/{id}', [ProfileController::class, 'readNotification']);
});

Route::middleware(['auth:sanctum', 'role:voter', 'role:contestant'])->group(function () {
    // Coin Management
    Route::get('/coins', [CoinController::class, 'index']);
    Route::get('/coins/balance', [CoinController::class, 'balance']);
    Route::post('/coins/purchase', [CoinController::class, 'purchase']);
    Route::get('/coins/history', [CoinController::class, 'history']);

    // Voting Interaction
    Route::post('/contests/{id}/vote', [VotingController::class, 'vote']);
});

// General Contests Routes
Route::prefix('contests')->controller(ContestController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::get('/{id}/contestants', 'contestants');
    Route::get('/{id}/leaderboard', 'contestLeaderboard');
});
// Leaderboards
Route::get('/leaderboards', [ContestController::class, 'leaderboards']);
Route::get('/leaderboards/{id}', [ContestController::class, 'contestLeaderboard']);
// guest voting
Route::post('/guest/{id}/vote', [VotingController::class, 'guestVote']);
