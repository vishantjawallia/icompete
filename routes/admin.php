<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CoinController;
use App\Http\Controllers\Admin\ContestController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\NotifyController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin-dash')->as('admin.')->group(function () {
    // Admin Auth
    Route::controller(AuthController::class)->middleware('admin.guest')->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'submitLogin')->name('login');
    });
    // reset Password
    Route::controller(PasswordController::class)->middleware('admin.guest')->group(function () {
        Route::get('/password/reset', 'resetView')->name('password.reset');
        Route::post('/password/reset', 'forgotPassword')->name('password.reset');
        Route::post('password/resend', 'resendCode')->name('password.resend');
        Route::get('/password/change', 'changePassword')->name('password.change');
        Route::post('password/confirm', 'resetPassword')->name('password.confirm');
    });

    Route::middleware('admin')->group(function () {

        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        // manage Admins

        Route::controller(AdminController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/profile', 'updateProfile')->name('profile');

            Route::get('/staffs', 'adminStaffs')->name('staffs');
            // Notifications
            Route::get('notifications', 'notifications')->name('notifications');
            Route::post('notifications/read/{id}', 'notificationRead')->name('notification.read');
            Route::get('notifications/open/{id}', 'notificationOpen')->name('notification.open');
            Route::delete('notifications/del/{id}', 'notificationDelete')->name('notification.delete');
            Route::get('notifications/read-all', 'readAllNotification')->name('notifications.readAll');
            Route::get('/notifications/ajax', 'ajaxNotifications')->name('notifications.ajax');
        });
        // Coins
        Route::controller(CoinController::class)->as('coin.')->prefix('coins')->group(function () {
            Route::get('/transactions', 'transactions')->name('transactions');
            Route::get('/settings', 'settings')->name('settings');
            Route::post('/settings', 'updateSettings')->name('settings');
        });
        // contests
        Route::controller(ContestController::class)->as('contest.')->prefix('contests')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/view/{id}', 'show')->name('show');
            Route::get('/approve/{id}', 'approve')->name('approve');
            Route::get('/reject/{id}', 'reject')->name('reject');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/delete/{id}', 'delete')->name('delete');
            // participants
            Route::get('/{id}/participants', 'participants')->name('participants');
            Route::get('/{id}/submissions', 'submissions')->name('submissions');
            // Others
            Route::get('/settings', 'settings')->name('settings');
            Route::get('/reports', 'reports')->name('reports');
            // voting
            Route::get('/{id}/votes', 'votes')->name('votes');
            Route::delete('/vote/{id}', 'removeVote')->name('votes.delete');
        });
        // submissions
        Route::controller(SubmissionController::class)->as('submission.')->prefix('submissions')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/entries', 'entry')->name('entry');
            Route::get('/view/{id}', 'show')->name('show');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/delete/{id}', 'delete')->name('delete');
            Route::get('/approve/{id}', 'approve')->name('approve');
            Route::get('/reject/{id}', 'reject')->name('reject');
            Route::get('/{id}/votes', 'votes')->name('votes');
        });

        // category
        Route::controller(CategoryController::class)->as('category.')->prefix('categories')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::post('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'delete')->name('delete');
        });

        // Community Posts
        Route::controller(PostController::class)->as('community.')->prefix('community')->group(function () {
            Route::get('/posts', 'index')->name('posts');
            Route::get('/posts/{id}', 'viewPost')->name('view');
            Route::put('/posts/{id}', 'updatePost')->name('update');
            Route::get('/posts/delete/{id}', 'deletePost')->name('delete');
            Route::get('/reports', 'reports')->name('reports');
            Route::get('/settings', 'settings')->name('settings');
            Route::get('/comments', 'comments')->name('comments');
            // update comment and delete
            Route::put('/comments/{id}', 'updateComment')->name('comment.update');
            Route::delete('/comments/delete/{id}', 'deleteComment')->name('comment.delete');
        });

        // Reports
        Route::controller(ReportController::class)->prefix('report')->as('reports.')->group(function () {
            Route::get('transactions', 'transactions')->name('transactions');
            Route::get('login/history', 'loginHistory')->name('login.history');
            Route::get('login/ipHistory/{ip}', 'loginIpHistory')->name('login.ipHistory');
            Route::get('login-history/del/{id}', 'loginHistoryDelete')->name('login.history.delete');
            Route::get('notifications', 'notificationHistory')->name('notifications');
            Route::get('notification/del/{id}', 'notificationDelete')->name('notifications.delete');
            Route::get('/payment-history', 'paymentHistory')->name('payment.history');
            Route::get('voting', 'voteHistory')->name('votes');
            Route::get('referral-commissions', 'commissions')->name('referrals');
        });

        // Users
        Route::controller(UserController::class)->as('users.')->prefix('users')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('active', 'active')->name('active');
            Route::get('contestants', 'contestants')->name('contestants');
            Route::get('organizers', 'organizers')->name('organizers');
            Route::get('voters', 'voters')->name('voters');
            Route::get('banned', 'banned')->name('banned');
            Route::get('email-verified', 'emailVerified')->name('email.verified');
            Route::get('email-unverified', 'emailUnverified')->name('email.unverified');

            Route::get('/view/{id}', 'view')->name('view');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::get('/verify/{id}', 'verify')->name('verify');
            Route::post('/notify/{id}', 'sendNotification')->name('notify');
            Route::post('/sendemail/{id}', 'sendemail')->name('sendmail');
            // Route::get('/login-user/{id}', 'userLogin')->name('login');
            Route::get('/delete/{id}', 'delete')->name('delete');
            Route::post('/ban/{id}', 'ban')->name('ban');
            Route::post('/unban/{id}', 'unban')->name('unban');
            Route::post('/update/{id}', 'update')->name('update');
            Route::post('/balance/{id}', 'updateBalance')->name('balance');
            Route::get('/settings', 'settings')->name('settings');
        });

        // Withdrawals
        Route::controller(WithdrawalController::class)->as('withdrawal.')->prefix('withdrawal')->group(function () {
            Route::get('/pending', 'pending')->name('pending');
            Route::get('/history', 'history')->name('history');
            Route::get('/settings', 'settings')->name('settings');

            Route::get('/approved/{id}', 'approve')->name('approve');
            Route::get('/reject/{id}', 'reject')->name('reject');
            Route::get('/delete/{id}', 'delete')->name('delete');
            Route::post('/auto-approval', 'bankApproval')->name('bank.approve');
            Route::get('/otp-page/{id}', 'otp')->name('otp');
            Route::post('/otp-page/{id}', 'processOtp')->name('otp.process');
        });

        // Notification Settings
        Route::controller(NotifyController::class)->as('notify.templates.')->prefix('notify/templates')->group(function () {
            Route::get('/', 'templates')->name('index');
            Route::get('/edit/{id}', 'editTemplate')->name('edit');
            Route::post('/update/{id}', 'updateTemplate')->name('update');

            Route::post('/test-email', 'testEmail')->name('test');
        });
        // Newsletter
        Route::controller(EmailController::class)->as('newsletter.')->prefix('newsletter')->group(function () {
            Route::get('/', 'newsletter')->name('index');
            Route::post('/store', 'storeNewsletter')->name('store');
            Route::get('/send/{id}', 'sendNewsletter')->name('send');
            Route::get('/add', 'addNewsletter')->name('add');
            Route::get('/edit/{id}', 'editNewsletter')->name('edit');
            Route::post('/edit/{id}', 'updateNewsletter')->name('edit');
            Route::get('/delete/{id}', 'deleteNewsletter')->name('delete');
            Route::post('/test-email', 'testEmail')->name('test');
        });

        // Email Templates
        Route::controller(EmailController::class)->as('email.templates.')->prefix('email/templates')->group(function () {
            Route::get('/', 'templates')->name('index');
            Route::get('/edit/{id}', 'editTemplate')->name('edit');
            Route::post('/update/{id}', 'updateTemplate')->name('update');

            Route::get('/create', 'createTemplate')->name('create');
            Route::post('/store', 'storeTemplate')->name('store');
        });

        // Settings
        Route::controller(SettingController::class)->as('settings.')->prefix('settings')->group(function () {
            Route::get('/custom-css-js', 'custom')->name('custom');
            Route::get('/', 'index')->name('index');
            Route::get('/system', 'system')->name('system');
            Route::get('/homepage', 'homepage')->name('homepage');
            Route::get('/email', 'email')->name('email');
            Route::get('/payment', 'payment')->name('payment');

            Route::post('/homepage/update', 'updateHome')->name('homepage.update');
            Route::post('/update', 'update')->name('update');
            Route::post('/system', 'systemUpdate')->name('sys_settings');
            Route::post('/system/store', 'storeSettings')->name('store_settings');
            Route::post('env_key', 'envkeyUpdate')->name('env_key');
        });

        // System
        Route::controller(AdminController::class)->prefix('system')->as('system.')->group(function () {
            Route::get('/clear-cache', 'clearCache')->name('cache');
            Route::get('/server-info', 'serverInfo')->name('server');
        });
    });
});
