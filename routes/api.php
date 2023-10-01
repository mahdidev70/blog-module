<?php

use TechStudio\Blog\app\Http\Controllers\ArticleController;
use TechStudio\Core\app\Http\Controllers\CategoriesController;
use TechStudio\Core\app\Http\Controllers\TagController;
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

// ============ CLIENT SIDE ===============
Route::prefix('articles')->group(function () {

    Route::get('/archive/common', [ArticleController::class, 'articlesArchiveCommon']);
    Route::get('/archive/list', [ArticleController::class, 'listArticles']);

});

// ============= PANEL SIDE ===============
// Route::middleware('login_required')->group(function () {

    Route::get('/article_editor/common', [ArticleController::class, 'getEditorCommon']);
    // Route::get('/article_editor/data/{id}', [ArticleController::class, 'getEditorData']);
    // Route::put('/article_editor/data/', [ArticleController::class, 'updateEditorData']);
    
    Route::prefix('panel')->group(function () {
    
        // Route::get('/articles/data', [ArticleController::class, 'getArticleListData']);
        Route::get('/articles/common', [ArticleController::class, 'getArticleListCommon']);
        // Route::put('/articles/set_status', [ArticleController::class, 'updateArticlesStatus']);
    
        // Route::post('/articles/upload_cover', [ArticleController::class, 'uploadArticleCover']);
        // Route::post('/articles/inline_media', [ArticleController::class, 'uploadArticleContent']);
        
        // ---- category side: ----
        Route::post('/categories/create', [CategoriesController::class, 'createCategory']);
        Route::get('/category/common', [CategoriesController::class, 'getCommonListCategory']);
        Route::get('/categories/list', [CategoriesController::class, 'listCategory']);
        Route::put('/categories/update', [CategoriesController::class, 'updateCategory']);
        Route::put('/categories/set_status', [CategoriesController::class, 'updateCategoryStatus']);
    
        // ---- tag side: ----
        // Route::post('/tags/create', 'App\Http\Controllers\TagController@createTags');
        // Route::get('/tag/common', 'App\Http\Controllers\TagController@getCommonListTag');
        // Route::get('/tags/list', 'App\Http\Controllers\TagController@listTags');
        // Route::put('/tags/update', 'App\Http\Controllers\TagController@updateTags');
        // Route::put('/tags/set_status', 'App\Http\Controllers\TagController@updateTagsStatus');
        // Route::get('/tag/list/search', 'App\Http\Controllers\TagController@tagSearch');
    
        // ----comment side: ----
        // Route::get('/articles/comments/data', 'App\Http\Controllers\CommentController@getArticleCommentsListData');
        // Route::get('/articles/comments/common', 'App\Http\Controllers\CommentController@getArticleCommentsListCommon');
        // Route::put('/articles/comments/update', 'App\Http\Controllers\CommentController@updateArticleCommentsStatus');
        // Route::put('/articles/comments/{comment_id}','App\Http\Controllers\CommentController@editArticleCommentText');
    
    });
// });



