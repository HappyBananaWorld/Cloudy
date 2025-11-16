<?php

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/',[IndexController::class,'index']);
Route::any('/tus{any?}', [IndexController::class,'upload'])
    ->name('upload')
    ->where('any', '.*')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

Route::get('list',[IndexController::class,'list']);
