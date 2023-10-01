<?php

use TechStudio\Blog\app\Http\Controllers\ArticleController;
use TechStudio\Core\app\Http\Controllers\CategoriesController;
use TechStudio\Core\app\Http\Controllers\TagController;
use TechStudio\Core\app\Http\Controllers\CommentController;
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

    // Route::get('/article_editor/common', [ArticleController::class, 'getEditorCommon']);
    // Route::get('/article_editor/data/{id}', [ArticleController::class, 'getEditorData']);
    // Route::put('/article_editor/data/', [ArticleController::class, 'updateEditorData']);
    
    // Route::prefix('panel')->group(function () {
    
        // Route::get('/articles/data', [ArticleController::class, 'getArticleListData']);
        // Route::get('/articles/common', [ArticleController::class, 'getArticleListCommon']);
        // Route::put('/articles/set_status', [ArticleController::class, 'updateArticlesStatus']);
    
        // Route::post('/articles/upload_cover', [ArticleController::class, 'uploadArticleCover']);
        // Route::post('/articles/inline_media', [ArticleController::class, 'uploadArticleContent']);
        
        // ---- category: ----
        // Route::post('/categories/create', [CategoriesController::class, 'createCategory']);
        // Route::get('/category/common', [CategoriesController::class, 'getCommonListCategory']);
        // Route::get('/categories/list', [CategoriesController::class, 'listCategory']);
        // Route::put('/categories/update', [CategoriesController::class, 'updateCategory']);
        // Route::put('/categories/set_status', [CategoriesController::class, 'updateCategoryStatus']);
    
        // ---- tag: ----
        // Route::post('/tags/create', [TagController::class, 'createTags']);
        // Route::get('/tag/common', [TagController::class, 'getCommonListTag']);
        // Route::get('/tags/list', [TagController::class, 'listTags']);
        // Route::put('/tags/update', [TagController::class, 'updateTags']);
        // Route::put('/tags/set_status', [TagController::class, 'updateTagsStatus']);
    
        // ---- comment: ----
        // Route::get('/articles/comments/data', [CommentController::class, 'getArticleCommentsListData']);
        // Route::get('/articles/comments/common', [CommentController::class, 'getArticleCommentsListCommon']);
        // Route::put('/articles/comments/update', [CommentController::class, 'updateArticleCommentsStatus']);
        // Route::put('/articles/comments/{comment_id}',[CommentController::class, 'editArticleCommentText']);
    
    // });
// });



