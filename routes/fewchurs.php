<?php

declare(strict_types=1);

use Fewchurs\Laravel\Http\Controllers\BoardController;
use Fewchurs\Laravel\Http\Controllers\ProxyController;
use Fewchurs\Laravel\Http\Middleware\VerifyHoneypot;
use Illuminate\Support\Facades\Route;

$id = '[A-Za-z0-9_-]{1,128}';
$writes = ['throttle:fewchurs'];

if (config('fewchurs.board.require_auth', false)) {
    $writes[] = 'auth';
}

Route::get('/', [BoardController::class, 'index'])->name('index');
Route::get('/features', [BoardController::class, 'features'])->name('features');
Route::get('/features/{feature}/comments', [BoardController::class, 'comments'])
    ->where('feature', $id)
    ->name('comments');

Route::middleware($writes)->group(function () use ($id) {
    Route::post('/features', [ProxyController::class, 'submit'])
        ->middleware(VerifyHoneypot::class)
        ->name('submit');

    Route::post('/features/{feature}/vote', [ProxyController::class, 'vote'])
        ->where('feature', $id)->name('vote');
    Route::delete('/features/{feature}/vote', [ProxyController::class, 'unvote'])
        ->where('feature', $id)->name('unvote');

    Route::post('/features/{feature}/follow', [ProxyController::class, 'follow'])
        ->where('feature', $id)->name('follow');
    Route::delete('/features/{feature}/follow', [ProxyController::class, 'unfollow'])
        ->where('feature', $id)->name('unfollow');

    Route::post('/features/{feature}/comments', [ProxyController::class, 'comment'])
        ->where('feature', $id)
        ->middleware(VerifyHoneypot::class)
        ->name('comment');
});
