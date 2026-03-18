<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountApiController;
use App\Http\Controllers\Api\V1\TransferApiController;
use App\Http\Controllers\Api\V1\LoanApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\CustomerApiController;
use App\Http\Controllers\Api\V1\SavingsApiController;
use App\Http\Controllers\Api\V1\BillApiController;
use App\Http\Controllers\Api\V1\BankApiController;

/*
|--------------------------------------------------------------------------
| API Routes — bankOS v1
|--------------------------------------------------------------------------
| All v1 routes are scoped under /api/v1/
| Tenant is resolved from X-Tenant-ID header via ApiTenantScope middleware
*/

Route::prefix('v1')->middleware(['api', 'api.tenant'])->group(function () {

    // ── AUTH ──────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('login',   [AuthController::class, 'login']);
        Route::post('logout',  [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('me',       [AuthController::class, 'me'])->middleware('auth:sanctum');
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
        Route::post('pin/verify', [AuthController::class, 'verifyPin'])->middleware('auth:sanctum');
    });

    // ── AUTHENTICATED ROUTES ──────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // CUSTOMER PROFILE
        Route::prefix('customer')->group(function () {
            Route::get('profile',          [CustomerApiController::class, 'profile']);
            Route::put('profile',          [CustomerApiController::class, 'updateProfile']);
            Route::post('kyc/upload',      [CustomerApiController::class, 'uploadKyc']);
            Route::post('pin/change',      [CustomerApiController::class, 'changePin']);
            Route::post('biometric',       [CustomerApiController::class, 'registerBiometric']);
            Route::get('notifications',    [CustomerApiController::class, 'notifications']);
        });

        // ACCOUNTS
        Route::prefix('accounts')->group(function () {
            Route::get('/',                [AccountApiController::class, 'index']);
            Route::get('{id}',             [AccountApiController::class, 'show']);
            Route::get('{id}/statement',   [AccountApiController::class, 'statement']);
            Route::get('{id}/balance',     [AccountApiController::class, 'balance']);
            Route::post('{id}/freeze',     [AccountApiController::class, 'freeze']);
            Route::post('{id}/unfreeze',   [AccountApiController::class, 'unfreeze']);
        });

        // TRANSFERS
        Route::prefix('transfers')->group(function () {
            Route::post('intrabank',       [TransferApiController::class, 'intrabank']);
            Route::post('interbank',       [TransferApiController::class, 'interbank']);
            Route::post('name-enquiry',    [TransferApiController::class, 'nameEnquiry']);
            Route::get('history',          [TransferApiController::class, 'history']);
            Route::get('{ref}/status',     [TransferApiController::class, 'status']);
        });

        // LOANS
        Route::prefix('loans')->group(function () {
            Route::get('/',                [LoanApiController::class, 'index']);
            Route::get('{id}',             [LoanApiController::class, 'show']);
            Route::get('products',         [LoanApiController::class, 'products']);
            Route::post('calculator',      [LoanApiController::class, 'calculate']);
            Route::post('apply',           [LoanApiController::class, 'apply']);
            Route::post('{id}/repay',      [LoanApiController::class, 'repay']);
            Route::get('{id}/schedule',    [LoanApiController::class, 'schedule']);
            Route::get('{id}/statement',   [LoanApiController::class, 'statement']);
        });

        // SAVINGS / POCKETS
        Route::prefix('savings')->group(function () {
            Route::get('/',                [SavingsApiController::class, 'index']);
            Route::post('/',               [SavingsApiController::class, 'create']);
            Route::get('{id}',             [SavingsApiController::class, 'show']);
            Route::post('{id}/fund',       [SavingsApiController::class, 'fund']);
            Route::post('{id}/withdraw',   [SavingsApiController::class, 'withdraw']);
        });

        // NOTIFICATIONS
        Route::prefix('notifications')->group(function () {
            Route::get('/',                [NotificationApiController::class, 'index']);
            Route::get('unread-count',     [NotificationApiController::class, 'unreadCount']);
            Route::post('{id}/read',       [NotificationApiController::class, 'markRead']);
            Route::post('read-all',        [NotificationApiController::class, 'markAllRead']);
            Route::post('fcm',             [NotificationApiController::class, 'registerFcm']);
        });

        // BILLS
        Route::prefix('bills')->group(function () {
            Route::get('billers',          [BillApiController::class, 'billers']);
            Route::post('validate',        [BillApiController::class, 'validate']);
            Route::post('pay',             [BillApiController::class, 'pay']);
            Route::get('history',          [BillApiController::class, 'history']);
        });

        // UTILITIES
        Route::get('banks',                [BankApiController::class, 'list']);
        Route::get('exchange-rates',       [BankApiController::class, 'exchangeRates']);
    });
});

// Legacy mobile API (kept for backwards compatibility)
Route::post('/mobile/login', [\App\Http\Controllers\Api\MobileController::class, 'login']);
Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::get('/balance',   [\App\Http\Controllers\Api\MobileController::class, 'balance']);
    Route::get('/statement', [\App\Http\Controllers\Api\MobileController::class, 'statement']);
    Route::get('/loans',     [\App\Http\Controllers\Api\MobileController::class, 'loanSummary']);
    Route::post('/repay',    [\App\Http\Controllers\Api\MobileController::class, 'repay']);
});
