<?php

namespace TechStudio\Blog\app\Http\Controllers;


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
    // public function getHomepage() {
    //     /*$articles = $this->articleService->getFeaturedArticles();*/
    //    $result =  [
    //           'featuredArticles' => $this->articleService->getFeaturedArticles(),
    //           'latestVideos' => $this->videoService->getLatestVideos(),
    //           'articles' => $this->articleService->getArticles('homepage'),
    //           'articlesNavbar' => $this->articleService->getArticlesNavbar(),
    //     ];
    //     return response()->json( $result,200);
    // }

    public function getHomeCommon()
    {
        // $lastPodcasts = Podcast::where('status','published')->select(['slug', 'title', 'banner_url', 'publicationDate', 'sound'])
        //     ->orderBy('publicationDate', 'DESC')
        //     ->take(12)
        //     ->get();

        $result = [
            'featuredArticles' => $this->articleService->getFeaturedArticles(),
            'quickActionBanners' => $this->bannerService->getBannerForHomPage(),
            // 'lastPodcasts' => $lastPodcasts,
            'categories' => $this->categoryService->getCategoriesForFilter(new Article()),
        ];
        return response()->json( $result,200);
    }

    public function getHomeData(Request $request)
    {
        return $this->articleService->getArticles(request:$request);
    }

}
