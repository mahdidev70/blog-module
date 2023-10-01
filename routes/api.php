<?php

use TechStudio\Blog\app\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('articles')->group(function () {

    Route::get('/archive/common', [ArticleController::class, 'articlesArchiveCommon']);
    Route::get('/archive/list', [ArticleController::class, 'listArticles']);

});