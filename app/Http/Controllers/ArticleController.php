<?php

namespace TechStudio\Blog\app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use TechStudio\Core\app\Models\Category;
use TechStudio\Core\app\Models\Tag;
use TechStudio\Blog\app\Models\Article;
use TechStudio\Blog\app\Services\Article\ArticleService;



// ====== Done : ========
// use App\Services\Article\ArticleService;
// use App\Models\Category;
// use App\Models\Article;
// use App\Models\Tag;


// ===== not done : =====
// use App\Helper\SlugGenerator;
// use App\Models\Alias;
// use App\Models\Bookmark;
// use App\Models\UserProfile;
// use App\Services\Category\CategoryService;
// use App\Services\File\FileService;
// use Illuminate\Database\Eloquent\ModelNotFoundException;
// use Illuminate\Validation\ValidationException;
// use App\Helper\ArrayPaginate;
// use Illuminate\Support\Facades\Auth;
// use Symfony\Component\HttpFoundation\Exception\BadRequestException;
// use Illuminate\Support\Facades\Validator;
// use App\Helper\HtmlContent;

class ArticleController extends Controller
{
    public function __construct(protected ArticleService $articleService)
    { }

    // private function authors()
    // {
    //     $user = Auth::user();

    //     $authorOptions = [
    //         [
    //             'displayName' => $user->getDisplayName(),
    //             'id' => $user->id,
    //             'type' => 'user',
    //         ]
    //     ];

    //     foreach (Alias::all() as $alias) {
    //         $authorOptions[] = [
    //             'displayName' => $alias->name,
    //             'id' => $alias->id,
    //             'type' => 'alias',
    //         ];
    //     }

    //     return $authorOptions;
    // }

    // public function getArticle(Article $slug)
    // {
    //     return $this->articleService->getArticle($slug);
    // }

    public function listArticles(Request $request)
    {
        // return $this->articleService->getArticles(request:$request);
    }

    public function articlesArchiveCommon()
    {
        // return [
        //     'pinnedArticles' => $this->articleService->pinnedArticles() , //DEPRECATED
            //ToDo CORE :
            // 'categories' => $this->categoryService->getCategoriesForFilter(new Article()),  
        // ];

    }

    // public function articlesSectionCommon()
    // {
    //     return [
    //         'pinnedArticles' => $this->articleService->pinnedArticles(),
    //     ];
    // }

    // public function articlesSectionList()
    // {
    //     $result = $this->articleService->getArticles('articles');
    //     return ArrayPaginate::paginate($result, 2);
    // }

    // public function storeFeedback(Article $slug,Request $request)
    // {
    //     if (!$request->has('action') || !in_array($request->action,['clear', 'like', 'dislike'])){
    //         throw new BadRequestException("'action' request data field must be either of [clear, like]."); // improve validation
    //     }
    //     $currentUserAction = $request->action;
    //     $functionName = strtolower($request->action).'By';
    //     $slug->$functionName(Auth::user()->id);
    //     return [
    //         'feedback' => [
    //             'likesCount' => $slug->likes_count??0,
    //             'currentUserAction' => $currentUserAction,
    //         ],
    //     ];
    // }

    // public function articlesByCategoryCommon(Category $slug)
    // {
    //     return  $this->articleService->getFirstArticleByCategory($slug);
    // }

    // public function storeBookmark(Article $slug,Request $request)
    // {
    //     if (!$request->has('action') || !in_array($request->action,['save','clear'])){
    //         throw new BadRequestException("'action' request data field must be either of [clear, save]."); // improve validation
    //     }
    //     $currentUserAction = $request->action;
    //     if ($request->action == 'clear'){
    //         $slug->clearBookmarkBy(Auth::user()->id);
    //     }else{
    //         $slug->saveBy(Auth::user()->id);
    //     }


    //     return [
    //         'bookmark' => [
    //             'currentUserAction' => $currentUserAction,
    //         ],
    //     ];
    // }

    public function getEditorCommon(Request $request)
    {
        $categories = Category::where('table_type','App\Models\Article')->get()->map(function ($category) {
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

        return [
            'categories' => $categories,
            'tags' => $tags,
            //ToDo Core
            // 'authorOptions' => $this->authors(),
        ];
    }

    // public function getEditorData($id)
    // {
        // $article = Article::with('tags', 'author')->where('id', $id)->firstOrFail();

        // if ($article->author_type == 'App\\Models\\Alias') {
        //     $article->author_type = 'alias';
        // }elseif ($article->author_type == 'App\\Models\\UserProfile') {
        //     $article->author_type = 'user';
        // }

        // $content = $article->content;
        // for ($i = 0; $i < count($content); $i++) {
        //     $block = $content[$i];
        //     if ($block['type'] == 'html' && is_string($block['content'])) {
        //         // legacy html block. upgrade it:
        //         $replacementBlocks = HtmlContent::htmlToBlocks($block['content']);
        //         array_splice($content, $i, 1);
        //         array_splice($content, $i, 0, $replacementBlocks);
        //     }
        // }

    //      return [
    //             'id' => $article->id,
    //             'title' => $article->title,
    //             'slug' => $article->slug,
    //             'bannerUrl' => $article->bannerUrl,
    //             'bannerUrlMobile' => $article->bannerUrlMobile,
    //             'summary' => $article->summary,
    //             'category' => $article->category['slug'],
    //             'tags' => $article->tags->map(function ($tag) {
    //                 return [
    //                     'title' => $tag->title,
    //                     'slug' => $tag->slug,
    //                 ];
    //             }),
    //             'content' => $content,
    //             'seoTitle' => $article->seoTitle,
    //             'seoKeyword' => $article->seoKeyword,
    //             'seoDescription' => $article->seoDescription,
    //             'publicationDate' => $article->publicationDate,
    //             'author' => [
    //                 'displayName' => $article->author->getDisplayName(),
    //                 'type' => $article->author_type,
    //                 'id' => $article->author->id,
    //             ],
    //         ];

    // }

    
    // public function updateEditorData(Request $request)
    // {
    //     $data = $request;

    //     if ($data['id']) {
    //         $article = Article::where('id', $request->id)->firstOrFail();
    //     } else {
    //         $article = new Article;
    //         $article->status = 'draft';
    //     }

    //     if ($data->author) {
    //         if ($data->author['type'] == 'user') {
    //             $author = UserProfile::where('id', $data->author['id'])->firstOrFail();
    //         } else if ($data->author['type'] == 'alias') {
    //             $author = Alias::where('id', $data->author['id'])->firstOrFail();
    //         } else {
    //             throw new BadRequestException("'author.type' request data field must be either of [user, alias].");
    //         }
    //     } else {
    //         $author = \Auth::user();
    //     }
    //     $article->author()->associate($author);

    //     $article->title = $data['title'];
    //     if (!$article->slug) {
    //         $article->slug = SlugGenerator::transform(($data['title']));
    //     }else{
    //         $article->slug = $data['slug'];
    //     }
    //     $article->bannerUrl = $data['bannerUrl'];
    //     $article->bannerUrlMobile = $data['bannerUrlMobile'];
    //     $article->summary = $data['summary'];

    //     $category = Category::where('slug', $data['category'])->firstOrFail();
    //     $article->category()->associate($category);

    //     if ($data['tags']) {
    //         $tagArray= [];
    //         foreach($data['tags'] as $tag){
    //             array_push($tagArray,$tag['slug']);
    //         }
    //         $tags = Tag::whereIn('slug', $tagArray)->get();
    //         if (count($tags) < count($data['tags'])) {
    //             $e = new ModelNotFoundException;
    //             $e->setModel(Tag::class);
    //             throw $e;
    //         }

    //         $article->tags()->sync($tags->pluck('id'));
    //     }
    
    //     $article->content = $data['content'] ?? [];

    //     $article->seoDescription = $data['seoDescription'];
    //     $article->seoTitle = $data['seoTitle'];
    //     $article->seoKeyword = $data['seoKeyword'];

    //     $article->publicationDate = $data['publicationDate'];

    //     $article->save();

    //     return ['id' => $article->id];

    // }

    // public function getArticleListData(Request $request)
    // {

    //     $query = Article::with('author', 'comments', 'category');

    //     if ($request->filled('search')) {
    //         $txt = $request->get('search');

    //         $query->where(function ($q) use ($txt) {
    //             $q->where('title', 'like', '%' . $txt . '%');
    //         });
    //     }

    //     //Filtering
    //     if (isset($request->authorId) && $request->authorId != null ) {
    //         $query->where('author_id', $request->input('authorId'));
    //     }

    //     if (isset($request->authorType) && $request->authorType != null) {
    //         if ($request->authorType == 'user') {
    //             $query->where('author_type', 'App\Models\UserProfile');
    //         }elseif ($request->authorType == 'alias') {
    //             $query->where('author_type', 'App\Models\Alias');
    //         }
    //     }

    //     if (isset($request->categorySlug) && $request->categorySlug != null) {
    //         $query->whereHas('category', function ($categoryQuery) use ($request) {
    //             $categoryQuery->where('slug', $request->input('categorySlug'));
    //         });
    //     }

    //     if (isset($request->publicationDateMax) && $request->publicationDateMax != null ) {
    //         $query->whereDate('publicationDate', '<=', $request->input('publicationDateMax'));
    //     }

    //     if (isset($request->publicationDateMin) && $request->publicationDateMin != null ) {
    //         $query->whereDate('publicationDate', '>=', $request->input('publicationDateMin'));
    //     }

    //     if (isset($request->status) && $request->status != null ) {
    //         $query->where('status', $request->input('status'));
    //     }

    //     if ($request->has('sort')) {
    //         if ($request->sort == 'bookmarks') {
    //             $query->orderByDesc('bookmarks_count');
    //         } elseif ($request->sort == 'views') {
    //             $query->orderByDesc('viewsCount');
    //         } elseif ($request->sort == 'comments') {
    //             $query->withCount('comments')->orderByDesc('comments_count');
    //         }
    //     }

    //     $query->orderByDesc('id');

    //     $articles = $query->paginate(10);

    //     $data = [
    //         'total' => $articles->total(),
    //         'per_page' => $articles->perPage(),
    //         'last_page' => $articles->lastPage(),
    //         'current_page' => $articles->currentPage(),
    //         'data' => []
    //     ];

    //     foreach ($articles as $article) {

    //         $commentsCount = $article->comments->count();

    //         $bookmark = $article->bookmarks->count();

    //         if ($article->author_type == 'App\\Models\\Alias') {
    //             $article->author_type = 'alias';
    //         }elseif ($article->author_type == 'App\\Models\\UserProfile') {
    //             $article->author_type = 'user';
    //         }

    //         $data['data'][] = [
    //             'id' => $article->id,
    //             'title' => $article->title,
    //             'author' =>[
    //                 'displayName' => $article->author->getDisplayName(),
    //                 'id' => $article->author->id,
    //                 'type' => $article->author_type,
    //             ],
    //             'category' =>  $article->category->slug,
    //             'commentsCount' => $commentsCount,
    //             'bookmarksCount' => $bookmark,
    //             'publicationDate' => $article->publicationDate,
    //             'viewsCount' => $article->viewsCount,
    //             'status' => $article->status,
    //             'id' => $article->id,
    //             'slug' => $article->slug,
    //         ];
    //     }

    //     return $data;
    // }

    public function getArticleListCommon(Request $request)
    {
        // ToDo Core
        // $id = Auth::user()->id;

        $category = Category::where('table_type','App\Models\Article')->get();

        $counts = [
            'all' => Article::whereNot('status', 'deleted')->count(),
            // 'mine' => Article::where('author_id', $id)->count(),
            'published' => Article::where('status', 'published')->count(),
            'draft' => Article::where('status', 'draft')->count(),
            'hidden' => Article::where('status', 'hidden')->count(),
            'deleted' => Article::where('status', 'deleted')->count(),
        ];

        $categories = $category->map(function ($category) {
            return [
                'title' => $category->title,
                'slug' => $category->slug,
            ];
        });

        $data = [
            'counts' => $counts,
            'categories' => $categories,
            // ToDo Core
            // 'authors' => $this->authors(),
            // If a status is added, it should be added here TODO
            'status' => [
                'published',
                'draft',
                'hidden',
                'deleted'
            ]
        ];

        return $data;

    }

    // public function updateArticlesStatus(Request $request, Article $article)
    // {

    //     $validatedData = $request->validate([
    //         'status' => 'required|in:published,hidden,deleted,draft',
    //         'ids' => 'required|array',
    //     ]);

    //     $ids = collect($validatedData['ids']);

    //     if ($validatedData['status'] == 'published') {
    //         $date = Carbon::now()->toDateTimeString();
    //         $articles = $article->whereIn('id', $ids)->get();

    //         foreach ($articles as $article) {


    //             $data = Validator::make($article->toArray(), [
    //                 //to do AmirMahdi
    //                 'title' => 'required',
    //                 'slug' => 'required', //BEDON SPACE -- MAX CHAR = 80 -- add slug generator
    //                 'content' => 'required',
    //                 'bannerUrl' => 'required',
    //                 'category_id' => 'required|integer',
    //                 'summary' => 'required',
    //                 // 'publicationDate' => 'required',
    //                 'viewsCount' => 'integer',
    //                 'author_id' => 'required|integer',
    //             ])->validate();
    //             // if (SlugGenerator::transform($article->slug) != $article->slug) {
    //             //     throw new BadRequestException("اسلاگ حاوی کارکتر های نامناسب است.");
    //             // }

    //             $article->whereIn('id', $ids)->update([
    //                 'status' => 'published',
    //                 'publicationDate' => $date,
    //             ]);
    //         }
    //     } else {
    //         $article->whereIn('id', $ids)->update(['status' => $validatedData['status']]);
    //     }

    //     return [
    //         'updatedArticles' => $ids,
    //     ];
    // }

    // public function uploadArticleCover(Request $request)
    // {
    //     $createdFiles = $this->fileService->upload(
    //         $request,
    //         max_count: 1,
    //         max_size_mb: 2,
    //         types: ['jpg', 'jpeg', 'png'],
    //         format_result_as_attachment: false,
    //         storage_key: 'blog',
    //     );
    //     return response()->json($createdFiles);
    // }

    // public function uploadArticleContent(Request $request)
    // {
    //     $createdFiles = $this->fileService->upload(
    //         $request,
    //         max_count: 500,
    //         max_size_mb: 1000,
    //         types: ['jpg', 'jpeg', 'png', 'mp4', 'mkv'],
    //         format_result_as_attachment: true,
    //         storage_key: 'blog',
    //     );
    //     return response()->json($createdFiles);
    // }

}
