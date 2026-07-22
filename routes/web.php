<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Public route for live podium (no auth required)
Route::get('/live-podium', [PollController::class, 'livePodium'])->name('polls.live-podium');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', [PollController::class, 'index'])->name('polls.index');
    Route::get('/polls/{poll}', [PollController::class, 'show'])->name('polls.show');
    Route::get('/polls/{poll}/results', [PollController::class, 'results'])->name('polls.results');
    Route::get('/polls/{poll}/ranking', [PollController::class, 'ranking'])->name('polls.ranking');
    Route::post('/polls/{poll}/vote', [VoteController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('polls.vote');
});
