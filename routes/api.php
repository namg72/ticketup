<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login']);


Route::middleware('auth:sanctum')->get('/tickets', [ApiTicketController::class, 'index']);
Route::middleware('auth:sanctum')->post('/tickets/create', [ApiTicketController::class, 'store']);
Route::middleware('auth:sanctum')->put('/tickets/update/{id}', [ApiTicketController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/tickets/delete/{id}', [ApiTicketController::class, 'destroy']);




Route::get('/test', function () {
    return "API OK";
});
