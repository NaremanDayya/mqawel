<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContractorController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemDamageController;
use App\Http\Controllers\Api\ItemMovementController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectExpenseController;
use App\Http\Controllers\Api\ProjectItemController;
use App\Http\Controllers\Api\ProjectWorkerController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\WorkerDamageController;
use App\Http\Controllers\Api\WorkerPauseDateController;
use App\Http\Controllers\Api\WorkerPaymentController;
use App\Http\Controllers\Api\WorkerProjectController;
use App\Http\Controllers\Api\WorkerWorkDayController;
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
    Route::get('/me/dashboard-preferences', [AuthController::class, 'dashboardPreferences']);
    Route::put('/me/dashboard-preferences', [AuthController::class, 'updateDashboardPreferences']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/search', [SearchController::class, 'index']);

    Route::get('/company', [CompanyController::class, 'show']);
    Route::put('/company', [CompanyController::class, 'update']);
    Route::get('/company/notification-settings/{section}', [CompanyController::class, 'notificationSettings']);
    Route::put('/company/notification-settings/{section}', [CompanyController::class, 'updateNotificationSettings']);
    Route::get('/company/dashboard-widgets', [CompanyController::class, 'dashboardWidgets']);
    Route::put('/company/dashboard-widgets', [CompanyController::class, 'updateDashboardWidgets']);

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
    Route::get('/workers/{worker}/damages', [WorkerDamageController::class, 'index']);
    Route::post('/workers/{worker}/damages', [WorkerDamageController::class, 'store']);
    Route::put('/workers/{worker}/damages/{damage}', [WorkerDamageController::class, 'update']);
    Route::delete('/workers/{worker}/damages/{damage}', [WorkerDamageController::class, 'destroy']);
    Route::get('/workers/{worker}/work-days', [WorkerWorkDayController::class, 'index']);
    Route::post('/workers/{worker}/work-days', [WorkerWorkDayController::class, 'store']);
    Route::delete('/workers/{worker}/work-days/{day}', [WorkerWorkDayController::class, 'destroy']);
    Route::get('/workers/{worker}/pause-dates', [WorkerPauseDateController::class, 'index']);
    Route::post('/workers/{worker}/pause-dates', [WorkerPauseDateController::class, 'store']);
    Route::delete('/workers/{worker}/pause-dates/{pause}', [WorkerPauseDateController::class, 'destroy']);
    Route::get('/workers/{worker}/projects', [WorkerProjectController::class, 'index']);

    Route::apiResource('projects', ProjectController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('/projects/{project}/expenses', [ProjectExpenseController::class, 'index']);
    Route::post('/projects/{project}/expenses', [ProjectExpenseController::class, 'store']);
    Route::put('/projects/{project}/expenses/{expense}', [ProjectExpenseController::class, 'update']);
    Route::delete('/projects/{project}/expenses/{expense}', [ProjectExpenseController::class, 'destroy']);
    Route::get('/projects/{project}/workers', [ProjectWorkerController::class, 'index']);
    Route::post('/projects/{project}/workers', [ProjectWorkerController::class, 'store']);
    Route::delete('/projects/{project}/workers/{worker}', [ProjectWorkerController::class, 'destroy']);
    Route::get('/projects/{project}/items', [ProjectItemController::class, 'index']);
    Route::post('/projects/{project}/items', [ProjectItemController::class, 'store']);
    Route::delete('/projects/{project}/items/{item}', [ProjectItemController::class, 'destroy']);

    Route::apiResource('contractors', ContractorController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::apiResource('storages', StorageController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::apiResource('items', ItemController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('/items/{item}/damages', [ItemDamageController::class, 'index']);
    Route::post('/items/{item}/damages', [ItemDamageController::class, 'store']);
    Route::put('/items/{item}/damages/{damage}', [ItemDamageController::class, 'update']);
    Route::delete('/items/{item}/damages/{damage}', [ItemDamageController::class, 'destroy']);

    Route::apiResource('item-categories', ItemCategoryController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::apiResource('item-movements', ItemMovementController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::apiResource('files', FileController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
});
