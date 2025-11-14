<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login']);


Route::middleware('auth:sanctum')->get('/tickets', [ApiTicketController::class, 'index']);



Route::get('/test', function () {
    return "API OK";
});
