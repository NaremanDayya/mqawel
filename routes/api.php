<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContractorController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\WorkerPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateMe']);
    Route::post('/me/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/company', [CompanyController::class, 'show']);
    Route::put('/company', [CompanyController::class, 'update']);
    Route::get('/company/notification-settings/{section}', [CompanyController::class, 'notificationSettings']);
    Route::put('/company/notification-settings/{section}', [CompanyController::class, 'updateNotificationSettings']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::apiResource('users', UserController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::apiResource('roles', RoleController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::get('/reports/workers', [ReportController::class, 'workers']);
    Route::get('/reports/workers/export', [ReportController::class, 'workersExport']);
    Route::get('/reports/worker-payments', [ReportController::class, 'workerPayments']);
    Route::get('/reports/worker-payments/export', [ReportController::class, 'workerPaymentsExport']);
    Route::get('/reports/expired-files', [ReportController::class, 'expiredFiles']);
    Route::get('/reports/expired-files/export', [ReportController::class, 'expiredFilesExport']);
    Route::get('/reports/project-expenses', [ReportController::class, 'projectExpenses']);
    Route::get('/reports/project-expenses/export', [ReportController::class, 'projectExpensesExport']);
    Route::apiResource('workers', WorkerController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::apiResource('workers.payments', WorkerPaymentController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::apiResource('projects', ProjectController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::apiResource('contractors', ContractorController::class)->only(['index', 'show']);
    Route::apiResource('storages', StorageController::class)->only(['index', 'show']);
    Route::apiResource('items', ItemController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::apiResource('files', FileController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
});
