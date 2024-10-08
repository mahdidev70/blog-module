<?php

namespace TechStudio\Blog\app\Services\Article;

use Illuminate\Support\Facades\Cache;
use TechStudio\Blog\app\Models\Article;
use Illuminate\Support\Facades\App;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use TechStudio\Blog\app\Http\Resources\ArticleResource;
use TechStudio\Blog\app\Repositories\Article\ArticleRepositoryInterface;
use TechStudio\Core\app\Models\Category;
use TechStudio\Core\app\Models\UserProfile;
use Illuminate\Support\Facades\Log;

// TODO: Needs cleanup/refactor

class ArticleService
{
    public function __construct(protected ArticleRepositoryInterface $articleRepository)
    {}
    
    public function getAuthor(UserProfile $user)
    {
       return [
            "displayName" => $user->getDisplayName(),
            "avatarUrl" => $user->avatar_url,
            "id" => $user->user_id,
        ];
    }

    public function getFeaturedArticles()
    {
        $language = App::currentLocale();

        $articles = Article::where('language', $language)
            ->where('type','article')
            ->with(['author:user_id,first_name,last_name,avatar_url', 'category:id,title,slug'])
            ->orderBy('publicationDate', 'DESC')
            ->take(4)
            ->get();

        return ArticleResource::collection($articles);
    }

    public function getRecentPodcasts()
    {
        return Article::where('type', 'podcast')
            ->select(['slug', 'title', 'bannerUrl', 'publicationDate', 'summary','author_id'])
            ->with(['author:user_id,first_name,last_name,avatar_url', 'category:id,title,slug'])
            ->orderBy('publicationDate', 'DESC')
            ->take(15)
            ->get()
            ->map(function ($article) {
                $article->author = $this->getAuthor($article->author);
                return $article;
            });
    }

    public function getArticles($slug=null,$request=null)
    {
        $language = App::currentLocale();
        $articlesQuery = Article::query()->where('language', $language)->with(['tags']);

        if ($request->has('category')  && $request->category != 'null' && $request->category != 'undefined' &&  strlen($request->category) > 0){
            if ($request->category !== 'all'){
                  $articlesQuery->whereHas('category',function ($query) use($request){
                      $query->whereIn('slug', explode(',', $request->category));
                  });
            }
        }

        if ($request->has('tag') && $request->tag != 'null' && $request->tag != 'undefined' && strlen($request->tag) > 0){
            $articlesQuery->whereHas('tags',function ($query) use($request){
                $query->whereIn('slug', explode(',', $request->tag));
            });
        }

        if (!$request->has('sort')){
            $articlesQuery->orderBy('publicationDate', 'DESC');
        } else {
            if ($request->sort == 'recent') {
                $articlesQuery->orderBy('publicationDate', 'DESC');
            } else if ($request->sort == 'views') {
                $articlesQuery->orderBy('viewsCount', 'DESC');
            } else if ($request->sort == 'likes') {
                // $articlesQuery->orderBy('likesCount', 'DESC');  TODO: implement likes sort
                $articlesQuery->withCount([
                    'likes' => function ($query) {
                        $query->where('likeable_type', 'TechStudio\Blog\app\Models\Article');
                    }
                ])->orderBy('likes_count', 'desc');
            } else {
                return response()->json(
                    ['message' => "Unexpected sorting parameter. Use 'recent', 'views' or 'likes'."], 400
                );
            }
        }
        if ($request->has('skip') && $request->skip != 0){
            if ($request->skip != 1) {
                return response()->json([
                    'message' => 'Skip can only be 0 or 1.',
                ], 422);
            }
            $first_article_id = $articlesQuery->pluck('id')->first();
            $articlesQuery = $articlesQuery->whereNot('id',$first_article_id);
        }

        $articlesQuery = $articlesQuery->paginate(12);
        return $this->generateResponse($articlesQuery);
    }

    public function generateResponse($articlesQuery)
    {
        return $articlesQuery->through(function($article) {
            $article->summary = $article->getSummary();
            $category= [
                'title' => 'دسته بندی نشده',
                'slug' => null,
            ];
            $tags = [];
            if ($article->category_id != 0 && !is_null($article->category_id)){
                $category = [
                    'title' => $article->category?->title ?? 'دسته بندی نشده',
                    'slug' =>  $article->category?->slug ?? null,
                ];
            }
            $tags = $article->tags->map(fn ($tag) => [
                'slug' => $tag?->slug,
                'title' => $tag?->title,
            ]);

            return [
                "art" => $article->id,
                'title' =>$article->title,
                'seoTitle' => $article->seoTitle,
                'slug' =>$article->slug,
                'bannerUrl' =>$article->bannerUrl,
                'publicationDate' =>$article->publicationDate,
                'summary' =>$article->summary,
                'author' => $this->getAuthor($article->author),
                'category' => $category,
                'tags' => $tags,
                "minutesToRead" => $article->minutesToRead(),
                "information" => $article->information,
                'type' => $article->type,

            ];
        });
    }

    public function getArticle($article, $locale)
    {
        // $article = $article->with('author')->firstOrFail();

        if (!is_null($article->tags)){
            $tags = $article->tags->map(fn ($tag) => [
                'slug' => $tag?->slug,
                'title' => $tag?->title,
            ]);
        }else{
            $tags = null;
        }

        $relatedModel = new Article();

        $ip = request()->getClientIp();

        $cacheKey = 'articleViewCount'.$article->slug;
        $minute = config('cache.short_time')??30;

        $views = Cache::get($cacheKey) ?? [];
        $views[$ip] = isset($views[$ip]) ? $views[$ip] + 1 : 1;

        Cache::put($cacheKey, $views);

        $user_id = null;
        if (Auth('sanctum')->user()) {
            $user_id = Auth('sanctum')->user()->id;
        }

        return [
            'title' => $article->title,
            'seoTitle' => $article->seoTitle,
            'publicationDate' => $article->publicationDate,
            'likesCount' => $article->likes_count??0,
            'currentUserLiked' => $user_id && (bool)$article->isLikedBy($user_id),
            'currentUserBookmarked' => $user_id && (bool)$article->isSavedBy($user_id),
            'viewsCount' => $article->viewsCount,
            'bannerUrl' => $article->bannerUrl,
            'content' => $article->content,
            'summary' => $article->getSummary(),
            'relevantContentCards' => $this->getRelevantContentCards($article, $relatedModel, $locale),
            'tags' => $tags,
            'author' => $this->getAuthor($article->author),
            "minutesToRead" => $article->minutesToRead(),
            'information' => json_decode($article->information),
        ];
    }

    public function getRelevantContentCards($model, $relatedModel, $locale)
    {
        $relevantArticlesIds = null;
        $relevantArticles = [];

        try {
            $response = Http::timeout(1)->get("http://recommendation-active/" . $model->id)->json();
            $relevantArticlesIds = $response?$response["similar_articles"]:[];
            $relevantArticles = $model::where('language', $locale)->with('category')->orderByDesc('publicationDate')->whereIn('id', $relevantArticlesIds)->limit(3)->get();

        } catch (\Exception $e) {
            \Log::warning('Recommendation system error. Reason: ' . $e);
        }

        if (count($relevantArticles) == 0) {
            $relevantArticles = $model::where('language', $locale)->with('category')->whereNot('id', $model->id)->inRandomOrder()->take(3)->get();
        }

        return collect($relevantArticles)->map(fn ($a) => [
            'bannerUrl' => $a->bannerUrl,
            'bannerUrlPodcast' => $a->banner_url,
            'publicationDate' => $a->publicationDate,
            'title' => $a->title,
            'summary' => method_exists($a, 'getSummary') ? $a->getSummary() : '',
            'slug' => $a->slug,
            'type' => $model instanceof TechStudio\Blog\app\Models\Article ? 'article' : 'podcast',
            "minutesToRead" => method_exists($a, 'minutesToRead') ? $a->minutesToRead() : '',
            'category' => [
                "slug" => $a->category ? $a->category->slug : 'no-category',
                "title" => $a->category ? $a->category->title : 'بدون دسته‌بندی',
            ],
            'author' => $this->getAuthor($a->author),
        ]);
    }

    public function pinnedArticles()
    {
        $language = App::currentLocale();

        $pinnedArticles = Article::take(2)->with('tags')->where('language', $language)->get();
        return $pinnedArticles->map(fn ($a) => [
            'bannerUrl' => $a->bannerUrl,
            'publicationDate' => $a->publicationDate,
            'title' => $a->title,
            'slug' => $a->slug,
            'summary' => $a->getSummary(),
            'author' => $this->getAuthor($a->author),
            'tags' => $a->tags->map(fn ($tag) => [
                'slug' => $tag->slug,
                'title' => $tag->title,
            ])
        ]);
    }

    public function getFirstArticleByCategory($category)
    {
        $categoryTitle = $category->title;
        $article = Article::where('category_id',$category->id)->latest('id')->first();
        $article->summary = $article->getSummary();  // error
        $category= [
            'title' => 'دسته بندی نشده',
            'slug' => null,
        ];
        $tags = [];
        if ($article->category_id != 0 && !is_null($article->category_id)){
            $category = [
                'title' => $article->category?->title ?? 'دسته بندی نشده',
                'slug' =>  $article->category?->slug ?? null,
            ];
        }
        $tags = $article->tags->map(fn ($tag) => [
            'slug' => $tag?->slug,
            'title' => $tag?->title,
        ]);

        return [
            'title' => $categoryTitle ,
            'featuredArticle' =>[
            'id' =>$article->id,
            'title' =>$article->title,
            'slug' =>$article->slug,
            'bannerUrl' =>$article->bannerUrl,
            'publicationDate' =>$article->publicationDate,
            'summary' =>$article->summary,
            'author' => $this->getAuthor($article->author),
            'category' => $category,
            'tags' => $tags,
            "minutesToRead" => $article->minutesToRead(),
            'information' => $article->information,
            ],
        ];
    }
}
