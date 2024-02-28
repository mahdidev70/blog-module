<?php

namespace TechStudio\Blog\app\Repositories\Article;

use Illuminate\Support\Facades\App;
use TechStudio\Blog\app\Models\Article;
use TechStudio\Core\app\Models\Category;

class ArticleRepository implements ArticleRepositoryInterface
{
    protected $model= Article::class;

    public function getCategoriesWithCourses($locale)
    {
        return Category::where('table_type', $this->model)->where('language', $locale)->get();
    }

    public function getArticleAuthors()
    {
        $object =  $this->model::query();
       return $object->with(['author'])->get()->unique('author_id');
    }

    public function getCommonCounts($userId)
    {
        $query = new Article();
        $all = $query->whereNot('status', 'deleted')->count();
        $mine = $query->where('author_id', $userId)->count();
        $published = $query->where('status', 'published')->count();
        $draft = $query->where('status', 'draft')->count();
        $hidden = $query->where('status', 'hidden')->count();
        $deleted = $query->where('status', 'deleted')->count();
        return [
            'all' => $all ,
            'mine' => $mine ,
            'published' => $published,
            'draft' => $draft,
            'hidden' => $hidden,
            'deleted' => $deleted
        ];
    }

    public function getAllArticles($request)
    {
        $language = App::currentLocale();
        $articlesQuery = Article::query()->where('language', $language)->with(['tags']);

        if ($request->filled('type')){
            if ($request['type'] == 'podcast'){
                $articlesQuery->where('type','podcast');
            }else{
                $articlesQuery->where('type','podcast');
            }
        }

        //این قسمت از فرانت اینگونه میاد واسه همین این شرط ها لازمه
        if ($request->has('category') && $request->category != 'null' && $request->category != 'undefined' && strlen($request->category) > 0){
        /*if ($request->filled('category')){*/
            if ($request->category !== 'all'){
                $articlesQuery->whereHas('category',function ($query) use($request){
                    $query->whereIn('slug', explode(',', $request->category));
                });
            }
        }

        if ($request->has('tag') && $request->tag != 'null' && $request->tag != 'undefined' && strlen($request->tag) > 0){
        /*if ($request->filled('tag')){*/
            $articlesQuery->whereHas('tags',function ($query) use($request){
                $query->whereIn('slug', explode(',', $request->tag));
            });
        }

        if ($request->has('sort')) {
            $sort = $request->sort;
            if ($sort === 'views') {
                $articlesQuery->orderBy('viewsCount', 'DESC');
            } else if ($sort === 'likes') {
                $articlesQuery->withCount([
                    'likes' => function ($query) {
                        $query->where('likeable_type', 'TechStudio\Blog\app\Models\Article');
                    }
                ])->orderBy('likes_count', 'desc');
            } else if ($request->sort == 'recent'){
                $articlesQuery->orderBy('publicationDate', 'DESC');
            }
        } else {
            $articlesQuery->orderBy('publicationDate', 'DESC');
        }

        if ($request->has('skip') && $request->skip !== 0 && $request->skip !== 1) {
            return response()->json(['message' => 'Skip can only be 0 or 1.'], 422);
        }

        if ($request->has('skip') && $request->skip === 1) {
            $first_article_id = $articlesQuery->pluck('id')->first();
            $articlesQuery->where('id', '!=', $first_article_id);
        }

        return $articlesQuery->paginate(12);
    }
}
