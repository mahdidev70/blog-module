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

    Route::get('/archive/common', [ArticleController::class, 'articlesArchiveCommon']);
    Route::get('/archive/list', [ArticleController::class, 'listArticles']);
    Route::get('/section/common', [ArticleController::class, 'articlesSectionCommon']);
    Route::get('/category/{slug}/common', [ArticleController::class, 'articlesByCategoryCommon']);
    
});

Route::prefix('{locale?}/api/home')->group(function() {
    
    Route::get('/common', [BlogHomeController::class, 'getHomeCommon']);
    Route::get('/data', [BlogHomeController::class, 'getHomeData']);
    
});


Route::middleware('login_optional')->prefix('article')->group(function() {
    
    // Route::get('/{slug}/comments', [CommentController::class, 'getComments']);
    // Route::get('/{slug}', [ArticleController::class, 'getArticle']);
    
});

Route::middleware('login_required')->group(function () {
    
    // if (Config::get('flags.community')) {
    //         Route::post('article/{slug}/bookmark', [ArticleController::class, 'storeBookmark']);
    //         Route::post('article/{slug}/feedback', [ArticleController::class, 'storeFeedback']);
    //         Route::post('/comments', [CommentController::class, 'store']);
    //         Route::post('/comments/{id}/feedback', [CommentController::class, 'storeFeedback']);
    // };
    
    
    // Route::prefix('article/{slug}')->group(function() {
        
    // });
    
    
    // ============= PANEL SIDE ===============
    // Route::get('/article_editor/common', [ArticleController::class, 'getEditorCommon']);
    // Route::get('/article_editor/data/{id}', [ArticleController::class, 'getEditorData']);
    // Route::put('/article_editor/data/', [ArticleController::class, 'updateEditorData']);

    Route::prefix('panel')->group(function () {
    
        // ---- article ----
        // Route::get('/data', [ArticleController::class, 'getArticleListData']);
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
    
    });
});



