<?php

use App\Http\Controllers\CoinController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\VotingController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

// include authentication routes
Route::prefix('auth')->group(function () {
    require __DIR__ . '/api/auth.php';
});

// Organizers routes
Route::prefix('organizer')->middleware(['auth:sanctum', 'role:organizer'])->group(function () {
    require __DIR__ . '/api/organizer.php';
});

// contestants routes
Route::prefix('contestant')->middleware(['auth:sanctum', 'role:contestant', 'email.verify'])->group(function () {
    require __DIR__ . '/api/contestant.php';
});

// voters routes
Route::prefix('voter')->middleware(['auth:sanctum', 'role:voter', 'email.verify'])->group(function () {
    require __DIR__ . '/api/voter.php';
});

// User Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Profile management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    // update fcm token
    Route::put('/fcm-token', [ProfileController::class, 'updateFcmToken']);
    // delete account
    Route::delete('/profile', [ProfileController::class, 'deleteAccount']);

    // Community Engagement
    Route::prefix('community')->controller(CommunityController::class)->middleware('auth.optional')->group(function () {
        // Posts
        Route::get('/posts', 'posts')->withoutMiddleware(['auth:sanctum', 'email.verify']);
        Route::get('/posts/{id}', 'showPost')->withoutMiddleware(['auth:sanctum', 'email.verify']);
        Route::get('/posts/{id}/comments', 'comment')->withoutMiddleware(['auth:sanctum', 'email.verify']);
    });
    Route::prefix('community')->controller(CommunityController::class)->middleware('email.verify')->group(function () {
        // Posts
        Route::get('/my-posts', 'myPosts');
        Route::post('/posts', 'createPost');
        Route::put('/posts/{id}', 'updatePost');
        Route::delete('/posts/{id}', 'deletePost');
        Route::post('/posts/{id}/like', 'toggleLike');
        // comments
        Route::post('/posts/{id}/comments', 'makeComment');
        Route::put('/comments/{id}', 'updateComment');
        Route::delete('/comments/{id}', 'deleteComment');
    });
    // withdrawals
    Route::prefix('withdrawals')->controller(WithdrawalController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'create');
        Route::get('/details', 'details');
        Route::get('/{id}', 'show');
        Route::post('/{id}/cancel', 'cancel');
        Route::put('/settings', 'updateSettings');
    });

    // Notifications
    Route::get('/notifications', [ProfileController::class, 'notifications']);
    Route::get('/notifications/unread', [ProfileController::class, 'unreadNotifications']);
    Route::put('/notifications/read/{id}', [ProfileController::class, 'readNotification']);
});

// Coin Management
Route::prefix('coins')->controller(CoinController::class)->middleware(['auth:sanctum', 'email.verify'])->group(function () {
    // Coin Information & Balance
    Route::get('/', 'index');
    Route::get('/balance', 'balance');
    Route::get('/history', 'history');
    // Coin Purchases & Transactions
    Route::post('/purchase', 'purchase');
    Route::post('/redeem', 'redeem');
});
Route::middleware(['auth:sanctum', 'role:voter,contestant', 'email.verify'])->group(function () {
    // Voting Interaction
    Route::post('/participants/{id}/vote', [VotingController::class, 'vote']);
});

// General Contests Routes
Route::prefix('contests')->controller(ContestController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('category', 'categories');
    Route::get('/filter', 'filterContest');
    Route::get('/home', 'featuredContests');
    Route::get('/{id}', 'show');
    Route::get('/{id}/participants', 'participants');
    Route::get('/{id}/leaderboard', 'contestLeaderboard');
});
Route::get('/participants/{id}', [ContestController::class, 'showParticipant']);
// Leaderboards
Route::get('/leaderboards', [ContestController::class, 'leaderboards']);
Route::get('/leaderboards/{id}', [ContestController::class, 'contestLeaderboard']);
// guest voting
Route::post('/guest/vote/{id}', [VotingController::class, 'guestVote']);

// Utility Controller
Route::prefix('utility')->controller(UtilityController::class)->group(function () {
    // upload images
    Route::post('upload', 'uploadAssets')->middleware('auth:sanctum');
    Route::post('upload-image', 'uploadImage')->middleware('auth:sanctum');
    // settings
    Route::get('settings', 'settings');
    // rewards after viewing ads
    Route::post('ads-reward', 'rewardAds')->middleware('auth.optional');
});

// webhook urls
Route::prefix('webhooks')->controller(PaymentController::class)->group(function () {
    Route::get('/paypal', 'paypalWebhook')->name('paypal.webhook');
    Route::get('/flutterwave', 'flutterWebhook')->name('flutter.webhook');
});

// Default 404 response
Route::fallback(function () {
    return response()->json([
        'status'  => 'error',
        'message' => 'API route not found',
    ], 404);
});
