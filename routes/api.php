<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiTicketController;
use App\Http\Controllers\Api\ApiUsersController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {

    //tickets
    Route::get('/tickets', [ApiTicketController::class, 'index']);
    Route::post('/ticket/create', [ApiTicketController::class, 'store']);
    Route::put('/ticket/update/{id}', [ApiTicketController::class, 'update']);
    Route::delete('/ticket/delete/{id}', [ApiTicketController::class, 'destroy']);
    Route::post('/ticket/status/{id}', [ApiTicketController::class, 'ticketStatus']);

    //user
    Route::get('/users', [ApiUsersController::class, 'index']);
    Route::get('/user/{id}', [ApiUsersController::class, 'show']);
    Route::post('user/create', [ApiUsersController::class, 'store']);
    Route::put('/user/update/{id}', [ApiUsersController::class, 'update']);
    Route::delete('user/delete/{id}', [ApiUsersController::class, 'destroy']);
    Route::post('user/change_password', [ChangePasswordController::class, 'update']);
});




Route::get('/test', function () {
    return "API OK";
});
