<?php

namespace TechStudio\Blog\app\Http\Controllers;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use TechStudio\Core\app\Services\Category\CategoryService;
use TechStudio\Blog\app\Services\Banner\BannerService;
use TechStudio\Blog\app\Services\Article\ArticleService;
use TechStudio\Blog\app\Models\Article;
use App\Http\Controllers\Controller;

use App\Models\Podcast;
use App\Services\Video\VideoService;
use Illuminate\Http\Request;

class BlogHomeController extends Controller
{
    public function __construct(protected ArticleService $articleService, protected CategoryService $categoryService
                                ,protected BannerService $bannerService)
    { }
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
        /*$result = [
            'featuredArticles' => $this->articleService->getFeaturedArticles(),
            'quickActionBanners' => $this->bannerService->getBannerForHomPage(),
            'categories' => $this->categoryService->getCategoriesForFilter(new Article()),
            'recentPodcasts' => $this->articleService->getRecentPodcasts()
        ];*/
        $minutes = config('cache.long_time')??10080;
        $locale = App::currentLocale();
        $cacheKey =  'articlesLandingPageCommon-' . $locale;
        $result = Cache::remember($cacheKey, $minutes, function () {
            return [
                'featuredArticles' => $this->articleService->getFeaturedArticles(),
                'quickActionBanners' => $this->bannerService->getBannerForHomPage(),
                'categories' => $this->categoryService->getCategoriesForFilter(new Article()),
                'recentPodcasts' => $this->articleService->getRecentPodcasts()
            ];
        });
        return response()->json( $result,200);
    }

    public function getHomeData(Request $request)
    {
        return $this->articleService->getArticles(request:$request);
    }

}
