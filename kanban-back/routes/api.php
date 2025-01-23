<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\BoardController;
use App\Http\Controllers\API\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware(['throttle:60,1'])->group(function () {
        Route::apiResource('/image', ImageController::class)
            ->only(['index', 'store', 'destroy']);
    });

    Route::middleware(['throttle:30,1'])->group(function () {
        Route::put('/projects/{project}/boards/positions', [BoardController::class, 'updatePositions']);
    });

    Route::middleware(['throttle:200,1'])->group(function () {
        Route::put('/projects/{project}/boards/{board}/tasks/positions', [TaskController::class, 'updatePositions']);
        Route::put('/projects/{project}/boards/tasks/{task}', [TaskController::class, 'updateBoards']);
    });

    Route::middleware(['throttle:100,1'])->group(function () {
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('projects.boards', BoardController::class);
        Route::apiResource('projects.boards.tasks', TaskController::class);
    });
});
