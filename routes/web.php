<?php

use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TicketController;
use App\Http\Controllers\Web\TicketImageController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [TicketController::class, 'index'])
        ->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets


    Route::get('/tickets/index', [TicketController::class, 'index'])
        ->name('tickets.index');


    Route::get('/tickets/create', [TicketController::class, 'create'])
        ->name('tickets.create');

    Route::post('/tickets/create', [TicketController::class, 'store'])
        ->name('tickets.store');

    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])
        ->name('tickets.edit');

    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])
        ->name('tickets.update');

    Route::post('/tickets/{ticket}/comment', [TicketController::class, 'createComment'])
        ->name('tickets.comments.store');

    Route::put('/tickets/{ticket}/comment/{comment}', [TicketController::class, 'updateComment'])
        ->name('tickets.comments.update');



    // Ticket image
    Route::get('/tickets/{ticket}/image', [TicketImageController::class, 'show'])
        ->name('tickets.image');

    Route::get('/tickets/{ticket}/image/download', [TicketImageController::class, 'download'])
        ->name('tickets.image.download');
});





require __DIR__ . '/auth.php';
