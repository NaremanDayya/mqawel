<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContractorController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\WorkerController;
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
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/company', [CompanyController::class, 'show']);

    Route::apiResource('workers', WorkerController::class)->only(['index', 'show']);
    Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);
    Route::apiResource('contractors', ContractorController::class)->only(['index', 'show']);
    Route::apiResource('storages', StorageController::class)->only(['index', 'show']);
    Route::apiResource('items', ItemController::class)->only(['index', 'show']);
    Route::apiResource('files', FileController::class)->only(['index', 'show']);
});
