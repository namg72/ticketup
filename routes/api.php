<?php

use App\Http\Controllers\Api\User\ApiUsersController;
use App\Http\Controllers\Api\Auth\ApiAuthController;

use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Category\ApiCategoryController;
use App\Http\Controllers\Api\Ticket\ApiTicketController;
use App\Http\Controllers\TicketComment\ApiTicketCommentTicketCommentyController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
    //user
    Route::get('/users', [ApiUsersController::class, 'index']);
    Route::get('/user/{id}', [ApiUsersController::class, 'show']);
    Route::post('user/create', [ApiUsersController::class, 'store']);
    Route::put('/user/update/{id}', [ApiUsersController::class, 'update']);
    Route::delete('user/delete/{id}', [ApiUsersController::class, 'destroy']);
    Route::post('user/change_password', [ChangePasswordController::class, 'update']);

    //tickets
    Route::get('/tickets', [ApiTicketController::class, 'index']);
    Route::post('/ticket/create', [ApiTicketController::class, 'store']);
    Route::put('/ticket/update/{id}', [ApiTicketController::class, 'update']);
    Route::delete('/ticket/delete/{id}', [ApiTicketController::class, 'destroy']);
    Route::post('/ticket/status/{id}', [ApiTicketController::class, 'ticketStatus']);
    Route::get('/ticket/{id}/image', [ApiTicketController::class, 'image']);
    Route::get('/ticket/show/{id}', [ApiTicketController::class, 'show']);

    //Comments
    Route::get('/ticket/show_Comments/{id}', [ApiTicketCommentTicketCommentyController::class, 'index']);
    Route::post('/ticket/create_comment/{id}', [ApiTicketCommentTicketCommentyController::class, 'store']);
    Route::put('/ticket/update_comment/{id}', [ApiTicketCommentTicketCommentyController::class, 'update']);
    Route::delete('/ticket/delete_comment/{id}', [ApiTicketCommentTicketCommentyController::class, 'destroy']);


    //catetories
    Route::get('/categories', [ApiCategoryController::class, 'index']);
    Route::post('/category/create', [ApiCategoryController::class, 'store']);
    Route::put('/category/update/{category}', [ApiCategoryController::class, 'update']);
    Route::get('/category/show/{id}', [ApiCategoryController::class, 'show']);
    Route::post('/category/changeStatus/{id}', [ApiCategoryController::class, 'changeStatus']);
});




Route::get('/test', function () {
    return "API OK";
});
