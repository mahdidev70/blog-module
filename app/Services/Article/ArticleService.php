<?php

namespace TechStudio\Blog\app\Services\Article;

use TechStudio\Blog\app\Models\Article;


use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use TechStudio\Core\app\Models\Category;

// TODO: Needs cleanup/refactor

class ArticleService
{

    public function getFeaturedArticles()
    {
        return Article::select(['slug', 'title', 'bannerUrl', 'publicationDate', 'summary'])
            ->orderBy('publicationDate', 'DESC')
            ->take(4)
            ->get()
            ->map(function ($article) {
                $article->author = [
                    "displayName" => 'دیجی‌کالا',
                    "avatarUrl" => 'https://storage.sa-test.techstudio.diginext.ir/static/digikala.png',
                    "id" => 45,
                ];  // TODO: replace with user display name
                return $article;
            });
    }

    public function getArticles($slug=null,$request=null) {
        // if ($slug){
        //     return Section::where('slug',$slug)->first()->expand();
        // }
        $articlesQuery = Article::query()->with(['tags']);

        if ($request->has('category') && strlen($request->category) > 0){
            if ($request->category !== 'all'){
                  $articlesQuery->whereHas('category',function ($query) use($request){
                      $query->whereIn('slug', explode(',', $request->category));
                  });
            }
        }

        if ($request->has('tag') && strlen($request->tag) > 0){
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
                        $query->where('likeable_type', 'App\Models\Article');
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
                "art" => $article->id,
                'title' =>$article->title,
                'slug' =>$article->slug,
                'bannerUrl' =>$article->bannerUrl,
                'publicationDate' =>$article->publicationDate,
                'summary' =>$article->summary,
                'author' => [
                    "displayName" => 'دیجی‌کالا',
                    "avatarUrl" => 'https://storage.sa-test.techstudio.diginext.ir/static/digikala.png',
                    "id" => 45,
                ],  // TODO: replace with user display name
                'category' => $category,
                'tags' => $tags,
                "minutesToRead" => $article->minutesToRead(),

            ];
        });
    }
    // public function getArticlesNavbar() {
    //     return Section::where('slug', 'homepage')->first()->toNavbar();
    // }

    public function getArticle($article)
    {
        if (!is_null($article->tags)){
            $tags = $article->tags->map(fn ($tag) => [
                'slug' => $tag?->slug,
                'title' => $tag?->title,
            ]);
        }else{
            $tags = null;
        }
        $article->increment('viewsCount');
        $user_id = Auth::user()?->id;
        return [
            'title' => $article->title,
            'publicationDate' => $article->publicationDate,
            'likesCount' => $article->likes_count??0,
            //ToDo AmirMahdi,
            // 'currentUserLiked' => $user_id && (bool)$article->isLikedBy($user_id),
            // 'currentUserBookmarked' => $user_id && (bool)$article->isSavedBy($user_id),
            'viewsCount' => $article->viewsCount,
            'bannerUrl' => $article->bannerUrl,
            'content' => $article->content,
            'summary' => $article->getSummary(),
            'relevantContentCards' => $this->getRelevantContentCards($article),
            'tags' => $tags,
            'author' => [
                "displayName" => 'دیجی‌کالا',
                "avatarUrl" => 'https://storage.sa-test.techstudio.diginext.ir/static/digikala.png',
                "id" => 28,
            ],  // TODO: replace with user display name
            "minutesToRead" => $article->minutesToRead(),
        ];

    }
    
    public function getRelevantContentCards(Article $article) {
        $relevantArticlesIds = null;
        try {
            $response = Http::timeout(1)->get("http://recommendation:5600/" . $article->id)->json();
            $relevantArticlesIds = $response["similar_articles"];
            $relevantArticles = Article::with('category')->orderByDesc('publicationDate')->whereIn('id', $relevantArticlesIds)->limit(3)->get();
            if (count($relevantArticles) == 0) {
                throw new \Exception('Received empty list from recommendation system.');
            }
        } catch (\Exception $e) {
            \Log::warning('Recommendation system error. Reason: ' . $e);
            $relevantArticles = Article::with('category')->orderByDesc('publicationDate')->take(3)->get();
        }
        return $relevantArticles->map(fn ($a) => [
            'bannerUrl' => $a->bannerUrl,
            'publicationDate' => $a->publicationDate,
            'title' => $a->title,
            'summary' => $a->getSummary(),
            'slug' => $a->slug,
            'type' => 'article',
            "minutesToRead" => $a->minutesToRead(),
            'category' => [
                "slug" => $a->category ? $a->category->slug : 'no-category',
                "title" => $a->category ? $a->category->title : 'بدون دسته‌بندی',
            ],
            'author' => [
                "displayName" => 'دیجی‌کالا',
                "avatarUrl" => 'https://storage.sa-test.techstudio.diginext.ir/static/digikala.png',
                "id" => 28,
            ],  // TODO: replace with user display name
        ]);
    }

    public function pinnedArticles()
    {
        $pinnedArticles = Article::take(2)->with('tags')->get();
        return $pinnedArticles->map(fn ($a) => [
            'bannerUrl' => $a->bannerUrl,
            'publicationDate' => $a->publicationDate,
            'title' => $a->title,
            'slug' => $a->slug,
            'summary' => $a->getSummary(),
            'author' => [
                "displayName" => 'دیجی‌کالا',
                "avatarUrl" => 'https://storage.sa-test.techstudio.diginext.ir/static/digikala.png',
                "id" => 28,
            ],  // TODO: replace with user display name
            'tags' => $a->tags->map(fn ($tag) => [
                'slug' => $tag->slug,
                'title' => $tag->title,
            ])
        ]);
    }

    public function getFirstArticleByCategory($category)
    {
        $category = Category::where('slug', $category)->whereNull('deleted_at')->firstOrFail();

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
                'author' => [
                    "displayName" => 'دیجی‌کالا',
                    "avatarUrl" => 'https://storage.sa-test.techstudio.diginext.ir/static/digikala.png',
                    "id" => 48,
                ],  // TODO: replace with user display name
                'category' => $category,
                'tags' => $tags,
                "minutesToRead" => $article->minutesToRead(),
            ],
            ];


    }
}
