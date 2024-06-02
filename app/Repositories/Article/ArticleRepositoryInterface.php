<?php

namespace TechStudio\Blog\app\Repositories\Article;

use TechStudio\Blog\app\Models\Article;

interface ArticleRepositoryInterface
{
    public function getAllArticles($request);
    public function getCategoriesWithCourses($locale) ;
    public function getArticleAuthors();
    public function getCommonCounts($userId);
    public function reject(array $parameters, $article): void;
}
