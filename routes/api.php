<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\AddMessageAction;
use App\Infrastructure\Http\Controllers\HistoryAction;
use App\Infrastructure\Http\Controllers\StatusAction;
use Illuminate\Support\Facades\Route;

Route::post('/add', AddMessageAction::class);
Route::get('/status/{uuid}', StatusAction::class);
Route::get('/history/recipient/{id}', HistoryAction::class);
