<?php

namespace TechStudio\Blog\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\Store\AuthorResource;
use App\Jobs\ConvertVideo;
use App\Jobs\ConvertAudio;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use TechStudio\Blog\app\Http\Requests\Article\RejectRequest;
use TechStudio\Blog\app\Http\Requests\Article\UpdateRequest;
use TechStudio\Blog\app\Repositories\Article\ArticleRepositoryInterface;
use TechStudio\Core\app\Models\Category;
use TechStudio\Core\app\Models\Tag;
use TechStudio\Blog\app\Models\Article;
use TechStudio\Core\app\Models\UserProfile;
use TechStudio\Blog\app\Services\Article\ArticleService;
use TechStudio\Core\app\Services\Category\CategoryService;
use TechStudio\Core\app\Services\File\FileService;
use TechStudio\Core\app\Models\Traits\taggeable;
use TechStudio\Core\app\Helper\ArrayPaginate;
use TechStudio\Core\app\Helper\HtmlContent;
use TechStudio\Core\app\Helper\SlugGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Carbon\Carbon;
use TechStudio\Blog\app\Http\Requests\UploadFileRequest;
use TechStudio\Blog\app\Http\Requests\UploadImageFileRequest;
use TechStudio\Blog\app\Http\Resources\ArticleResource;
use TechStudio\Blog\app\Http\Resources\ArticleSideBarResource;
use TechStudio\Blog\app\Http\Resources\ArticlesResource;
use TechStudio\Blog\app\Http\Resources\AthorResource;
use TechStudio\Core\app\Models\Bookmark;
use TechStudio\Core\app\Models\Follow;
use Illuminate\Support\Facades\DB;

// ===== not done : =====
// use App\Models\Bookmark;
// use Illuminate\Validation\ValidationException;

// use App\Helper\HtmlContent;

class ArticleController extends Controller
{
    public function __construct(protected ArticleService $articleService, protected CategoryService $categoryService,
                                protected FileService $fileService, protected ArticleRepositoryInterface $articleRepository)
    {}

    public function getArticle($locale, $slug, Request $request)
    {
        $type = $request['type'];
        $getArticle = Article::with('author')->where('slug', $slug)->where('language', $locale)->firstOrFail();
        return $this->articleService->getArticle($getArticle, $locale, $type);
    }

    public function listArticles(Request $request)
    {
        if ($request->category == 'following') {

            if (Auth('sanctum')->user()) {
                $user_id = Auth('sanctum')->user()->id;
            }
            $locale = App::currentLocale();

            $followingIds = Follow::where('follower_id', $user_id)->pluck('following_id');
            $articles = Article::whereIn('author_id', $followingIds)
                ->where('language', $locale)->paginate(10);

            return new ArticlesResource($articles);
        }
        $minutes = config('cache.mid_time')??720;
      /*  $locale = App::currentLocale();*/
        $encode = md5(json_encode($request->all()));
        $cacheKey =  'articlesLandingPage_' . $encode;
        return Cache::remember($cacheKey, $minutes, function () use($request) {
            $articles =  $this->articleRepository->getAllArticles($request);
            return $this->articleService->generateResponse($articles);
        });
    }

    public function articlesArchiveCommon()
    {
        return [
            'categories' => $this->categoryService->getCategoriesForFilter(new Article()),

        ];
    }

    public function articlesSectionCommon()
    {
        return [
            'pinnedArticles' => $this->articleService->pinnedArticles(),
        ];
    }

    public function articlesSectionList()
    {
        $result = $this->articleService->getArticles('articles');
        return ArrayPaginate::paginate($result, 2);
    }

    public function storeFeedback($locale, $slug, Request $request)
    {
        $slug = Article::where('slug', $slug)->where('language', $locale)->firstOrFail();

        if (!$request->has('action') || !in_array($request->action, ['clear', 'like', 'dislike'])) {
            throw new BadRequestException("'action' request data field must be either of [clear, like]."); // improve validation
        }

        $currentUserAction = $request->action;
        $functionName = strtolower($request->action) . 'By';
        $slug->$functionName(auth()->user()->id);

        return [
            'feedback' => [
                'likesCount' => $slug->likes_count ?? 0,
                'currentUserAction' => $currentUserAction,
            ],
        ];
    }

    public function articlesByCategoryCommon($locale, Category $slug)
    {
        return  $this->articleService->getFirstArticleByCategory($slug);
    }

    public function storeBookmark($locale, $slug, Request $request)
    {
        $slug = Article::where('slug', $slug)->where('language', $locale)->firstOrFail();

        if (!$request->has('action') || !in_array($request->action, ['save', 'clear'])) {
            throw new BadRequestException("'action' request data field must be either of [clear, save]."); // improve validation
        }

        $currentUserAction = $request->action;
        if ($request->action == 'clear') {
            $slug->clearBookmarkBy(Auth::user()->id);
        } else {
            $slug->saveBy(Auth::user()->id);
        }


        return [
            'bookmark' => [
                'currentUserAction' => $currentUserAction,
            ],
        ];
    }

    public function getEditorCommon(Request $request)
    {
        $articleModel = new Article();

        $categories = Category::where('table_type', get_class($articleModel))->get()->map(function ($category) {
            return [
                'title' => $category->title,
                'slug' => $category->slug,
            ];
        });

        $tags = Tag::all()->map(function ($tag) {
            return [
                'title' => $tag->title,
                'slug' => $tag->slug,
            ];
        });

        $authors = UserProfile::select('user_id', 'first_name', 'last_name')->get();

        $authors = $authors->map(function ($author) {
            return [
                'id' => $author->user_id,
                'displayName' => $author->getdisplayName(),
                'type' => 'user',
            ];
        });


        return [
            'categories' => $categories ?? '',
            'tags' => $tags,
            'authorOptions' => $authors,
        ];
    }

    public function getEditorData($locale, $id)
    {
        $article = Article::with('tags', 'author')->where('id', $id)->where('language', $locale)->firstOrFail();

        $user = auth()->user();
        if (! $user->can('blogs') && $article->author_id != $user->id) {
            abort(403, 'Access denied');
        }

        $userModel = new UserProfile();

        if ($article->author_type == get_class($userModel)) {
            $article->author_type = 'user';
        }

        $content = $article->content;
        for ($i = 0; $i < count($content); $i++) {
            $block = $content[$i];
            if ($block['type'] == 'html' && is_string($block['content'])) {
                // legacy html block. upgrade it:
                $replacementBlocks = HtmlContent::htmlToBlocks($block['content']);
                array_splice($content, $i, 1);
                array_splice($content, $i, 0, $replacementBlocks);
            }
        }

        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'bannerUrl' => $article->bannerUrl,
            'bannerUrlMobile' => $article->bannerUrlMobile,
            'summary' => $article->summary,
            'category' => $article->category['slug'] ?? "",
            'tags' => $article->tags->map(function ($tag) {
                return [
                    'title' => $tag->title,
                    'slug' => $tag->slug,
                ];
            }),
            'content' => $content,
            'seoTitle' => $article->seoTitle,
            'seoKeyword' => $article->seoKeyword,
            'seoDescription' => $article->seoDescription,
            'publicationDate' => $article->publicationDate,
            'author' => [
                'displayName' => $article->author->getDisplayName(),
                'type' => 'user',
                'id' => $article->author->user_id,
            ],
            'information' => json_decode($article->information),
        ];
    }


    public function updateEditorData($locale, UpdateRequest $request)
    {
        if ($request['id']) {
            $article = Article::where('id', $request->id)->where('language', $locale)->firstOrFail();
        } else {
            $article = new Article;
        }

        $article->status = 'draft';

        if ($request->author) {
            $author = UserProfile::where('user_id', $request->author['id'])->firstOrFail();
        } else {
            $author = Auth::user();
        }
        $article->author()->associate($author);

        $article->title = $request['title'];
        if (!$article->slug) {
            $article->slug = SlugGenerator::transform(($request['title']));
        } else {
            $article->slug = $request['slug'];
        }

        $article->bannerUrl = $request['bannerUrl'];
        $article->bannerUrlMobile = $request['bannerUrlMobile'];
        $article->summary = $request['summary'];

        if ($request['category'] == "") {
            $article->category_id = NULL;
        } else {
            $category = Category::where('slug', $request['category'])->firstOrFail();
            $article->category()->associate($category);
        }

        if ($request['tags']) {
            $tagArray = [];
            foreach ($request['tags'] as $tag) {
                array_push($tagArray, $tag['slug']);
            }
            $tags = Tag::whereIn('slug', $tagArray)->get();
            if (count($tags) < count($request['tags'])) {
                $e = new ModelNotFoundException;
                $e->setModel(Tag::class);
                throw $e;
            }
            $article->tags()->sync($tags->pluck('id'));
        }

        $article->content = $request['content'] ?? [];

        if ($request['type'] == 'podcast') {
            $article->type = 'podcast';
        }

        $article->seoDescription = $request['seoDescription'];
        $article->seoTitle = $request['seoTitle'];
        $article->seoKeyword = $request['seoKeyword'];

        $article->information = json_encode($request['information']) ?? [];

        $article->publicationDate = $request['publicationDate'];

        $article->save();

        foreach ($request['content'] as $i => $block) {
            // if (isset($block['content']['url']) && !str_starts_with($block['content']['url'], env('FILE_PREFIX', 'https://storage-demo-seller-hub.digikala.com'))) {
            //     throw ValidationException::withMessages(['url' => 'The url must started with write file prefix.']);
            // }

            if (
                $block['type'] == 'vid' &&
                !filter_var(
                    $block['content']['url'],
                    FILTER_VALIDATE_URL
                )
            ) {
                Log::info("dispatch video job");
                ConvertVideo::dispatch(
                    $article,
                    $block['content']['url'],
                    $article->title
                );
            }

            if (
                $block['type'] == 'audio' &&
                !filter_var(
                    $block['content']['url'],
                    FILTER_VALIDATE_URL
                )
            ) {
                Log::info("dispatch audio job");
                ConvertAudio::dispatch(
                    $article,
                    $block['content']['url'],
                    $article->title
                );
            }
        }

        if (isset($request['final']) && $request['final'] == 1) {
            $date = Carbon::now()->toDateTimeString();

            Validator::make($article->toArray(), [
                //to do AmirMahdi
                'title' => 'required',
                'slug' => 'required', //BEDON SPACE -- MAX CHAR = 80 -- add slug generator
                'content' => 'required',
                'bannerUrl' => 'required',
                'category_id' => [
                    'required_if:' . $article->type . ', article', 'integer', 'nullable'],
                'summary' => 'required',
                'viewsCount' => 'integer',
                'author_id' => 'required|integer',
            ])->validate();
//             if (SlugGenerator::transform($article->slug) != $article->slug) {
//                 throw new BadRequestException("اسلاگ حاوی کارکتر های نامناسب است.");
//             }

            $message = 'پست منتشر شد.';

            if (auth()->user()->can('blogs')) {
                $article->publicationDate = $date;
                $article->status = 'published';
            } else {
                $message = 'پست در انتظار تایید برای انتشار است.';
                $article->status = 'waiting_for_approve';
            }

            $article->save();

            return response()->json(["data" => [], "message" => $message], 200);
        }


        return ['id' => $article->id];
    }

    public function getArticleListData($locale, Request $request)
    {
        $query = Article::where('language', $locale)->with('author', 'comments', 'category');

        $userModel = new UserProfile();

        if ($request->filled('search')) {
            $txt = $request->get('search');

            $query->where(function ($q) use ($txt) {
                $q->where('title', 'like', '%' . $txt . '%');
            });
        }

        //Filtering
        if (isset($request->authorId) && $request->authorId != null) {
            $query->where('author_id', $request->input('authorId'));
        }

        if (isset($request->authorType) && $request->authorType != null) {
            if ($request->authorType == 'user') {
                $query->where('author_type', get_class($userModel));
            }
        }

        if (isset($request->categorySlug) && $request->categorySlug != null) {
            $query->whereHas('category', function ($categoryQuery) use ($request) {
                $categoryQuery->where('slug', $request->input('categorySlug'));
            });
        }

        if (isset($request->publicationDateMax) && $request->publicationDateMax != null) {
            $query->whereDate('publicationDate', '<=', $request->input('publicationDateMax'));
        }

        if (isset($request->publicationDateMin) && $request->publicationDateMin != null) {
            $query->whereDate('publicationDate', '>=', $request->input('publicationDateMin'));
        }

        if (isset($request->status) && $request->status != null) {
            $query->where('status', $request->input('status'));
        }

        $sortOrder = 'desc';
        if (isset($request->sortOrder) && ($request->sortOrder ==  'asc' || $request->sortOrder ==  'desc')) {
            $sortOrder = $request->sortOrder;
        }

        if ($request->has('sortKey')) {
            if ($request->sortKey == 'lastUpdate') {
                $query->orderBy('updated_at', $sortOrder);
            } elseif ($request->sortKey == 'bookmarks') {
                $query->withCount('bookmarks')->orderBy('bookmarks_count', $sortOrder);
            } elseif ($request->sortKey == 'views') {
                $query->orderBy('viewsCount', $sortOrder);
            } elseif ($request->sortKey == 'comments') {
                $query->withCount('comments')->orderBy('comments_count', $sortOrder);
            }
        }

        $articles = $query->orderBy('id', $sortOrder)->paginate(10);

        $data = [
            'total' => $articles->total(),
            'per_page' => $articles->perPage(),
            'last_page' => $articles->lastPage(),
            'current_page' => $articles->currentPage(),
            'data' => []
        ];

        foreach ($articles as $article) {

            $commentsCount = $article->comments->count();
            $bookmark = $article->bookmarks->count();

            if ($article->author_type == get_class($userModel)) {
                $article->author_type = 'user';
            }

            $data['data'][] = [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'author' => [
                    'displayName' => $article->author->getDisplayName(),
                    'id' => $article->author->user_id,
                    'type' => $article->author_type,
                ],
                'category' =>  $article->category->slug ?? "",
                'commentsCount' => $commentsCount,
                'bookmarksCount' => $bookmark,
                'publicationDate' => $article->publicationDate,
                'viewsCount' => $article->viewsCount,
                'status' => $article->status,
                'information' => $article->information,
            ];
        }
        return $data;
    }

    public function getArticleListCommon($locale, Request $request)
    {
        $id = Auth::user()->id;
        $articleModel = new Article();

        $category = Category::where('table_type', get_class($articleModel))->where('language', $locale)->get();

        $counts = [
            'all' => $articleModel->whereNot('status', 'deleted')->count(),
            'mine' => $articleModel->where('author_id', $id)->count(),
            'published' => $articleModel->where('status', 'published')->count(),
            'draft' => $articleModel->where('status', 'draft')->count(),
            'hidden' => $articleModel->where('status', 'hidden')->count(),
            'deleted' => $articleModel->where('status', 'deleted')->count(),
            'rejected' => $articleModel->where('status', 'rejected')->count(),
        ];

        $categories = $category->map(function ($category) {
            return [
                'title' => $category->title,
                'slug' => $category->slug,
            ];
        });


        $authors = $articleModel->get()->unique('author_id')->pluck('author');

        $authors = $authors->map(function ($author) {
            return [
                'id' => $author->id ?? null,
                'displayName' => $author->getdisplayName() ?? null,
                'type' => 'user',
            ];
        });

        $data = [
            'counts' => $counts,
            'categories' => $categories,
            'authors' => $authors,
            'status' => [
                'published',
                'draft',
                'hidden',
                'deleted',
                'rejected',
            ]
        ];

        return $data;
    }

    public function updateArticlesStatus($locale, Article $article, Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:published,waiting_for_approve,hidden,deleted,draft',
            'ids' => 'required|array',
        ]);

        $ids = collect($validatedData['ids']);
        
        if ($validatedData['status'] == 'published') {
            $date = Carbon::now()->toDateTimeString();
            $articles = $article->whereIn('id', $ids)->where('language', $locale)->get();
            foreach ($articles as $article) {
                Validator::make($article->toArray(), [
                    //to do AmirMahdi
                    'title' => 'required',
                    'slug' => 'required', //BEDON SPACE -- MAX CHAR = 80 -- add slug generator
                    'content' => 'required',
                    'bannerUrl' => 'required',
                    'category_id' => [
                    'required_if:'.$article->type.', article', 'integer', 'nullable'],
                    'summary' => 'required',
                    'viewsCount' => 'integer',
                    'author_id' => 'required|integer',
                ])->validate();
                // if (SlugGenerator::transform($article->slug) != $article->slug) {
                //     throw new BadRequestException("اسلاگ حاوی کارکتر های نامناسب است.");
                // }
                $article->whereIn('id', $ids)->update([
                    'status' => 'published',
                    'publicationDate' => $date,
                ]);
            }
        } else {
            $article->whereIn('id', $ids)->update(['status' => $validatedData['status']]);
        }

        return [
            'updatedArticles' => $ids,
        ];
    }

    public function uploadArticleCover($locale, UploadImageFileRequest $request)
    {
        $createdFiles = $this->fileService->upload(
            $request,
            max_count: 1,
            max_size_mb: 2,
            types: ['jpg', 'jpeg', 'png', 'mp4', 'mkv', 'pdf', 'webp'],
            format_result_as_attachment: false,
            storage_key: 'blog',
        );
        return response()->json($createdFiles);
    }

    public function uploadArticleContent($locale, UploadFileRequest $request)
    {
        $createdFiles = $this->fileService->upload(
            $request,
            max_count: 500,
            max_size_mb: 1000,
            types: ['jpg', 'jpeg', 'png', 'mp4', 'mkv', 'pdf', 'webp'],
            format_result_as_attachment: true,
            storage_key: 'blog',
        );
        return response()->json($createdFiles);
    }

    public function findArticleList()
    {
        $articles = Article::whereIn(
            'id',
            explode(',', request()->get('ids'))
        )->orderByDesc('publicationDate')->get();
        return response()->json(ArticleResource::collection($articles));
    }

    public function getUserArticle($locale, Request $request)
    {
        $user = auth()->user();
        $articleModle = new Article();

        if ($request['data'] == 'my') {

            $myArticles = Article::where('author_id', $user->id)->orderBy('id', 'DESC')->paginate(10);
            return new ArticlesResource($myArticles);
        } elseif ($request['data'] == 'bookmark') {

            $bookmarks = Bookmark::where('bookmarkable_type', get_class($articleModle))
                ->where('user_id', $user->id)->orderBy('id', 'DESC')->pluck('bookmarkable_id');
            $articleBookmarks = Article::whereIn('id', $bookmarks)->paginate(10);

            return new ArticlesResource($articleBookmarks);
        }
    }

    public function knsPosts(Request $request) 
    {
        $locale = App::currentLocale();
        $data = Article::where('author_id', $request->userId)->where('language', $locale)->orderBy('id', 'DESC')->paginate(10);
        return new ArticlesResource($data);
    }

    public function reject(RejectRequest $request, $locale, $id)
    {
        $article = $this->articleRepository->reject($request->validated(), $id);

        return response()->json(["data" => new ArticleResource($article), "message" => "عملیات با موفقیت انجام شد."], 200);
    }
}
