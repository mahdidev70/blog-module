<?php

namespace TechStudio\Blog\app\Repositories\Article;

interface ArticleRepositoryInterface
{
    public function getCategoriesWithCourses($locale) ;

    public function getArticleAuthors();

    public function getCommonCounts($userId);
}
