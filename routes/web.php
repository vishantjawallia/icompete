<?php

use App\Http\Controllers\CronjobController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Admin Routes
require __DIR__ . '/admin.php';

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// test
Route::get('/test', [HomeController::class, 'testHome']);

// payment Page
Route::get('/payment-success', [HomeController::class, 'paymentSuccess'])->name('pay.success');
Route::get('/payment-failed', [HomeController::class, 'paymentError'])->name('pay.error');

// Payment Callbacks
Route::controller(PaymentController::class)->prefix('payments')->group(function () {
    Route::get('/paypal', 'paypalSuccess')->name('paypal.success');
    Route::get('/paypal-cancel', 'paypalError')->name('paypal.cancel');
    Route::get('/flutterwave', 'flutterSuccess')->name('flutter.success');
});

// Cronjobs
Route::controller(CronjobController::class)->prefix('cron-jobs')->as('cron.')->group(function () {
    Route::get('/all', 'initCronjob')->name('all');
    Route::get('/contests', 'contestCron')->name('contests');
    Route::get('/commands', 'runCommands')->name('commands');
    Route::get('/emails', 'scheduledEmail')->name('emails');
    Route::get('/delete-logins', 'deleteLoginHistory')->name('delete.logins');
    Route::get('/daily-backup', 'runBackup')->name('backup');
});

Route::get('/queue-jobs', function () {
    // Execute the cron job
    Artisan::call('queue:work', ['--stop-when-empty' => true]);
    Artisan::call('queue:retry all');

    return response()->json(['message' => 'Queue processed successfully']);
})->name('queue.work');
// cron for webocket server
Route::get('/reverb-server', function () {
    // * * * * * php /home/zenovate/demo.jadesdev.com.ng/icompete/artisan reverb:start
    return response()->json(['message' => 'Reverb server running']);
})->name('reverb.server');
