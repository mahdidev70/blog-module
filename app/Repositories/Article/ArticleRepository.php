<?php

namespace TechStudio\Blog\app\Repositories\Article;

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
        $query = $this->model::query();
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
}
