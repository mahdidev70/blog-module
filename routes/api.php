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

// Route::prefix('{locale?}/api')->group(function () {

    // ============ CLIENT SIDE ===============
    Route::prefix('articles')->group(function () {

        Route::get('/archive/common', [ArticleController::class, 'articlesArchiveCommon']); //=> Done
        Route::get('/archive/list', [ArticleController::class, 'listArticles']); //=> Done
        Route::get('/section/common', [ArticleController::class, 'articlesSectionCommon']); //=> Done
        Route::get('/category/{slug}/common', [ArticleController::class, 'articlesByCategoryCommon']); //=> Done
        
    });

    Route::prefix('home')->group(function() {
        
        Route::get('/common', [BlogHomeController::class, 'getHomeCommon']); //=> Done
        Route::get('/data', [BlogHomeController::class, 'getHomeData']); //=> Done
        
    });

    Route::middleware('login_optional')->prefix('article')->group(function() {
        
        Route::get('/{slug}/comments', [CommentController::class, 'getComments']);
        Route::get('/{slug}', [ArticleController::class, 'getArticle']); 
    });

    Route::middleware('login_required')->group(function () {
        
        if (Config::get('flags.community')) {
            Route::prefix('article/{slug}')->group(function() {
                Route::post('/bookmark', [ArticleController::class, 'storeBookmark']);
                Route::post('/feedback', [ArticleController::class, 'storeFeedback']);
                Route::post('/comments', [CommentController::class, 'store']);
                Route::post('/comments/{id}/feedback', [CommentController::class, 'storeFeedback']);
                    
            });
        };
        
        
        // ============= PANEL SIDE ===============
        // Route::get('/article_editor/common', [ArticleController::class, 'getEditorCommon']); // Done
        // Route::get('/article_editor/data/{id}', [ArticleController::class, 'getEditorData']); // Done
        // Route::put('/article_editor/data/', [ArticleController::class, 'updateEditorData']); 

        Route::prefix('panel')->group(function () {
        
            // ---- article ----
            // Route::get('/data', [ArticleController::class, 'getArticleListData']); // Done
            // Route::get('/articles/common', [ArticleController::class, 'getArticleListCommon']); // Done
            // Route::put('/articles/set_status', [ArticleController::class, 'updateArticlesStatus']); // Done
            // Route::post('/articles/upload_cover', [ArticleController::class, 'uploadArticleCover']);
            // Route::post('/articles/inline_media', [ArticleController::class, 'uploadArticleContent']);
            
            // ---- category: ----
            // Route::post('/categories/create', [CategoriesController::class, 'createCategory']); // Done
            // Route::get('/category/common', [CategoriesController::class, 'getCommonListCategory']); // Done
            // Route::get('/categories/list', [CategoriesController::class, 'listCategory']); // Done
            // Route::put('/categories/update', [CategoriesController::class, 'updateCategory']); // Done
            // Route::put('/categories/set_status', [CategoriesController::class, 'updateCategoryStatus']); // Done
        
            // ---- tag: ----
            // Route::post('/tags/create', [TagController::class, 'createTags']); // Done
            // Route::get('/tag/common', [TagController::class, 'getCommonListTag']); // Done
            Route::get('/tags/list', [TagController::class, 'listTags']);
            Route::put('/tags/update', [TagController::class, 'updateTags']);
            // Route::put('/tags/set_status', [TagController::class, 'updateTagsStatus']); // Done
        
            // ---- comment: ----
            // Route::get('/articles/comments/data', [CommentController::class, 'getArticleCommentsListData']); // Done
            // Route::get('/articles/comments/common', [CommentController::class, 'getArticleCommentsListCommon']); // Done
            // Route::put('/articles/comments/update', [CommentController::class, 'updateArticleCommentsStatus']); // Done
            // Route::put('/articles/comments/{comment_id}',[CommentController::class, 'editArticleCommentText']); // Done
        
        });
    });


// });

