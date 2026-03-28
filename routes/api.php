<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Ticket routes
    Route::apiResource('tickets', TicketController::class);
    
    // Custom ticket actions
   
    Route::patch('/tickets/{ticket}/change-status',   [TicketController::class, 'changeStatus']);
    Route::patch('/tickets/{ticket}/change-priority',  [TicketController::class, 'changePriority']);
    Route::patch('/tickets/{ticket}/assign-agent',     [TicketController::class, 'assignAgent']);
    Route::patch('/tickets/{ticket}/reopen',           [TicketController::class, 'reopen']);
    });