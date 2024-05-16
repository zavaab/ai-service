<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\DatawarController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api', 'prefix' => 'v1'], function () {
    Route::post('login', [AuthenticationController::class, 'store']);
    Route::post('logout', [AuthenticationController::class, 'destroy'])->middleware('auth:api');
    
    // Route::post('ai-send', [AiController::class, 'store'])->middleware('auth:api');
    // Route::post('ai-result', [AiController::class, 'result'])->middleware('auth:api');
    Route::post('ai-send', [AiController::class, 'store']);
    Route::post('ai-result', [AiController::class, 'result']);

    
    Route::post('call-to-rout', [DatawarController::class, 'calltorout']);

  });
