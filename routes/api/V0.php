<?php

use App\Http\Controllers\Api\V0\AuthController;
use App\Http\Controllers\Api\V0\EntryController;
use App\Http\Controllers\Api\V0\UserController;
use App\Http\Controllers\Api\V0\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->namespace('\App\Http\Controllers\Api')->group(function () {
    Route::get('/user/{uid}', [UserController::class, 'getUser'])->name('api.user.getUser');
    Route::patch('/user/{uid}', [UserController::class, 'updateUserWithoutIdUFFS'])->name('api.user.updateUserWithoutIdUFFS');
    Route::patch('/user/iduffs/{uid}', [UserController::class, 'updateUserWithIdUFFS'])->name('api.user.updateUserWithIdUFFS');
    Route::put('/user/{uid}', [UserController::class, 'changeUserActivity'])->name('api.user.changeUserActivity');
    Route::put('/user', [UserController::class, 'changeUserType'])->name('api.user.changeUserType');
    Route::post('/user', [UserController::class, 'createUserWithoutIdUFFS'])->name('api.user.createWithoutIdUFFS');
    Route::post('/forgot-password/{uid}', [AuthController::class, 'forgotPassword'])->name('api.auth.forgotPassword');
    Route::post('/reset-password/{uid}', [AuthController::class, 'resetPassword'])->name('api.auth.resetPassword');
    Route::get('/entry/{uid}', [EntryController::class, 'getEntries'])->name('api.entry.getEntries');
    Route::get('/ticket/{uid}', [TicketController::class, 'getTickets'])->name('api.ticket.getTickets');
    Route::get('/ticket/balance/{uid}', [TicketController::class, 'getTicketBalance'])->name('api.ticket.getTicketBalance');

    Route::middleware(\App\Http\Middleware\ThirdPartyCashierEmployeeMiddleware::class)->namespace('\App\Http\Controllers\Api')->group(function () {
        Route::post('/ticket/visitor', [TicketController::class, 'insertTicketsForVisitors'])->name('api.ticket.insertTicketsForVisitors');
        Route::post('/ticket/{enrollment_id}', [TicketController::class, 'insertTickets'])->name('api.ticket.insertTickets');
    });
});

Route::middleware(\App\Http\Middleware\ApiKeyMiddleware::class)->namespace('\App\Http\Controllers\Api')->group(function () {
    Route::post('/entry/{enrollment_id}', [EntryController::class, 'insertEntry'])->name('api.entry.insertEntry');
});

Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
Route::post('/user/iduffs', [UserController::class, 'createUserWithIdUFFS'])->name('api.user.createWithIdUFFS');
Route::get('/user/operation/{uid}', [UserController::class, 'getUserOperationStatus'])->name('api.user.getUserOperationStatus');