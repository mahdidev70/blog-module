<?php

namespace TechStudio\Blog\app\Http\Controllers;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use TechStudio\Core\app\Services\Category\CategoryService;
use TechStudio\Blog\app\Services\Banner\BannerService;
use TechStudio\Blog\app\Services\Article\ArticleService;
use TechStudio\Blog\app\Models\Article;
use App\Http\Controllers\Controller;
use App\Services\Video\VideoService;
use Illuminate\Http\Request;
use TechStudio\Blog\app\Http\Resources\ArticleResource;
use TechStudio\Blog\app\Http\Resources\AthorResource;
use TechStudio\Core\app\Models\UserProfile;
use TechStudio\Core\app\Repositories\BannerRepository;
use TechStudio\Core\app\Repositories\Interfaces\BannerRepositoryInterface;

class BlogHomeController extends Controller
{

    public function __construct(protected ArticleService $articleService, protected CategoryService $categoryService,
    protected BannerRepositoryInterface $bannerRepository)
    { 
        $this->bannerRepository = $bannerRepository;
    }
    public function getHomepage() {
    //     /*$articles = $this->articleService->getFeaturedArticles();*/
       $result =  [
              'featuredArticles' => $this->articleService->getFeaturedArticles(),
              'latestVideos' => $this->videoService->getLatestVideos(),
              'articles' => $this->articleService->getArticles('homepage'),
              'articlesNavbar' => $this->articleService->getArticlesNavbar(),
        ];
        return response()->json( $result,200);
    }

    public function getHomeCommon()
    {
        $minutes = config('cache.long_time')??10080;
        $locale = App::currentLocale();
        $cacheKey =  'articlesLandingPageCommon-' . $locale;
        $result = Cache::remember($cacheKey, $minutes, function () {
            $locale = App::currentLocale();
            $articles = Article::where('star', 1)->where('language', $locale)->take(5)->get();
            $authors = UserProfile::withCount('articles')->orderByDesc('articles_count')->take(10)->get();
            return [
                'featuredArticles' => $this->articleService->getFeaturedArticles(),
                'quickActionBanners' => $this->bannerRepository->getBannerForHomPage(),
                'categories' => $this->categoryService->getCategoriesForFilter(new Article()),
                'recentPodcasts' => $this->articleService->getRecentPodcasts(),
                'articleStar' => ArticleResource::collection($articles),
                'popularAuthor' => AthorResource::collection($authors),
            ];
        });
        return response()->json( $result,200);
    }
    

    public function getHomeData(Request $request)
    {
        return $this->articleService->getArticles(request:$request);
    }

}
