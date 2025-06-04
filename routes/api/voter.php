<?php

// Voters routes

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VotingController;

// Profile management
Route::get('/profile', [ProfileController::class, 'show']);
Route::put('/profile', [ProfileController::class, 'update']);

// Voting Interaction
Route::post('/contests/{id}/vote', [VotingController::class, 'vote'])->middleware('email.verify');
