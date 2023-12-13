<?php

use TechStudio\Blog\app\Http\Controllers\ArticleController;
use TechStudio\Blog\app\Http\Controllers\BlogHomeController;
use TechStudio\Core\app\Http\Controllers\CategoriesController;
use TechStudio\Core\app\Http\Controllers\TagController;
use TechStudio\Core\app\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;


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

        Route::get('/archive/common', [ArticleController::class, 'articlesArchiveCommon']); //=> Done
        Route::get('/archive/list', [ArticleController::class, 'listArticles']); //=> Done
        Route::get('/category/{slug}/common', [ArticleController::class, 'articlesByCategoryCommon']); //=> Done
        // Route::get('tag/{slug}/common', [ArticleController::class, 'articlesByCategoryCommon']);
        Route::get('find/list', [ArticleController::class, 'findArticleList']); //Done

    });

    Route::prefix('home')->group(function() {
        
        Route::get('/common', [BlogHomeController::class, 'getHomeCommon']); //=> Done
        Route::get('/data', [BlogHomeController::class, 'getHomeData']); //=> Done
        
    });

    Route::prefix('article')->group(function() {
        
        Route::get('/{slug}/comments', [CommentController::class, 'getComments']); //=> Done
        Route::get('/{slug}', [ArticleController::class, 'getArticle']); //=> Done

    });
    
    Route::middleware('auth:sanctum')->group(function () {

        
        Route::prefix('article/{slug}')->group(function() {
            
            Route::post('/bookmark', [ArticleController::class, 'storeBookmark']); //=> Done
            Route::post('/feedback', [ArticleController::class, 'storeFeedback']); //=> Done
            Route::post('/comments', [CommentController::class, 'store']); //=> Done
            Route::post('/comments/{id}/feedback', [CommentController::class, 'storeFeedback']); //=> Done
                    
        });
        
        // ============= PANEL SIDE ===============
        
        Route::prefix('article_editor')->group(function (){

            Route::get('/common', [ArticleController::class, 'getEditorCommon']); //=> Done
            Route::get('/data/{id}', [ArticleController::class, 'getEditorData']); //=> Done
            Route::put('/data', [ArticleController::class, 'updateEditorData']); //=> Done

        });

        Route::prefix('panel')->group(function () {
        
            // ---- article ----
            Route::prefix('/articles')->group(function () {
                
                Route::get('/data', [ArticleController::class, 'getArticleListData']); //=> Done
                Route::get('/common', [ArticleController::class, 'getArticleListCommon']); //=> Done
                Route::put('/set_status', [ArticleController::class, 'updateArticlesStatus']); //=> Done
                Route::post('/upload_cover', [ArticleController::class, 'uploadArticleCover']);
                Route::post('/inline_media', [ArticleController::class, 'uploadArticleContent']);

            });
            
            // ---- category ----
            Route::get('/category/common', [CategoriesController::class, 'getCommonListCategory']); //=> Done
            Route::get('/categories/list', [CategoriesController::class, 'listCategory']); //=> Done
            Route::put('/categories/update', [CategoriesController::class, 'createUpdateCategory']); //=> Done
            Route::put('/categories/set_status', [CategoriesController::class, 'updateCategoryStatus']); //=> Done
        
            // ---- tag ----
            Route::get('/tag/common', [TagController::class, 'getCommonListTag']); //=> Done
            Route::get('/tags/list', [TagController::class, 'listTags']); //=> Done
            Route::put('/tags/update', [TagController::class, 'createUpdateTags']); //=> Done
            Route::put('/tags/set_status', [TagController::class, 'updateTagsStatus']); //=> Done
        
            // ---- comment ----
            Route::get('/articles/comments/data', [CommentController::class, 'getArticleCommentsListData']); //=> Done
            Route::get('/articles/comments/common', [CommentController::class, 'getArticleCommentsListCommon']); //=> Done
            Route::put('/articles/comments/update', [CommentController::class, 'updateArticleCommentsStatus']); //=> Done
            Route::put('/articles/comments/{comment_id}',[CommentController::class, 'editArticleCommentText']); //=> Done
        
        });
    });
