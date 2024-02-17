<?php

namespace TechStudio\Blog\app\Providers;

use Illuminate\Support\ServiceProvider;
use TechStudio\Blog\app\Repositories\Article\ArticleRepository;
use TechStudio\Blog\app\Repositories\Article\ArticleRepositoryInterface;
use TechStudio\Lms\app\Repositories\CourseRepository;
use TechStudio\Lms\app\Repositories\ChapterRepository;
use TechStudio\Lms\app\Repositories\CommentRepository;
use TechStudio\Lms\app\Repositories\LessonRepository;
use TechStudio\Lms\app\Repositories\StudentRepository;
use TechStudio\Lms\app\Repositories\UserRepository;
use TechStudio\Lms\app\Repositories\CategoryLmsRepository;
use TechStudio\Lms\app\Repositories\Interfaces\CategoryLmsRepositoryInterface;
use TechStudio\Lms\app\Repositories\Interfaces\ChapterRepositoryInterface;
use TechStudio\Lms\app\Repositories\Interfaces\CommentRepositoryInterface;
use TechStudio\Lms\app\Repositories\Interfaces\CourseRepositoryInterface;
use TechStudio\Lms\app\Repositories\Interfaces\LessonRepositoryInterface;
use TechStudio\Lms\app\Repositories\Interfaces\SkillRepositoryInterface;
use TechStudio\Lms\app\Repositories\Interfaces\StudentRepositoryInterface;
use TechStudio\Lms\app\Repositories\Interfaces\UserRepositoryInterface;
use TechStudio\Lms\app\Repositories\SkillRepository;

class BlogRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
