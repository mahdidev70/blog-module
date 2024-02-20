<?php

namespace TechStudio\Blog\app\Services\Article;

use TechStudio\Blog\app\Models\Article;
use Illuminate\Support\Facades\App;


use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use TechStudio\Blog\app\Repositories\Article\ArticleRepositoryInterface;
use TechStudio\Core\app\Models\Category;
use TechStudio\Core\app\Models\UserProfile;

// TODO: Needs cleanup/refactor

class ArticleService
{
    public function __construct(protected ArticleRepositoryInterface $articleRepository){}
    public function getAuthor(UserProfile $user)
    {
       return [
            "displayName" => $user->getDisplayName(),
            "avatarUrl" => $user->avatar_url,
            "id" => $user->id,
        ];
    }

    public function getFeaturedArticles()
    {
        $language = App::currentLocale();

        return Article::where('language', $language)->select(['slug', 'title', 'bannerUrl', 'publicationDate', 'summary', 'author_id'])
            ->with('author')
            ->orderBy('publicationDate', 'DESC')
            ->take(4)
            ->get()
            ->map(function ($article) {
                $article->author->displayName = $this->getAuthor($article->author)['displayName'];
                return $article;
        });
    }

    public function getRecentPodcasts()
    {
        return Article::where('type', 'podcast')
            ->select(['slug', 'title', 'bannerUrl', 'publicationDate', 'summary','author_id'])
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
        $articlesQuery = $this->articleRepository->getAllArticles($request);
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
            if ( $article->category_id != 0 ){
                $category = [
                    'title' => $article->category->title,
                    'slug' =>  $article->category->slug,
                ];
            }
            $tags = $article->tags->map(fn ($tag) => [
                'slug' => $tag?->slug,
                'title' => $tag?->title,
            ]);

            return [
                "art" => $article->id,
                'title' =>$article->title,
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

    public function getArticle($article)
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
        $article->increment('viewsCount');
        if(auth()->check()){
            $user_id = auth()->id();
        }

        return [
            'title' => $article->title,
            'publicationDate' => $article->publicationDate,
            'likesCount' => $article->likes_count??0,
            'currentUserLiked' => $user_id && (bool)$article->isLikedBy($user_id),
            'currentUserBookmarked' => $user_id && (bool)$article->isSavedBy($user_id),
            'viewsCount' => $article->viewsCount,
            'bannerUrl' => $article->bannerUrl,
            'content' => $article->content,
            'summary' => $article->getSummary(),
            'relevantContentCards' => $this->getRelevantContentCards($article, $relatedModel),
            'tags' => $tags,
            'author' => $this->getAuthor($article->author),
            "minutesToRead" => $article->minutesToRead(),
            'information' => json_decode($article->information),
        ];
    }

    public function getRelevantContentCards($model, $relatedModel)
    {
        $relevantArticlesIds = null;
        $relevantArticles = [];

        try {
            $response = Http::timeout(1)->get("http://recommendation:5600/" . $model->id)->json();
            $relevantArticlesIds = $response?$response["similar_articles"]:[];
            $relevantArticles = $model::with('category')->orderByDesc('publicationDate')->whereIn('id', $relevantArticlesIds)->limit(3)->get();

        } catch (\Exception $e) {
            \Log::warning('Recommendation system error. Reason: ' . $e);
        }

        if (count($relevantArticles) == 0) {
            $relevantArticles = $model::with('category')->whereNot('id', $model->id)->inRandomOrder()->take(3)->get();
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
        if ( $article->category_id != 0 ){
            $category = [
                'title' => $article->category->title,
                'slug' =>  $article->category->slug,
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
