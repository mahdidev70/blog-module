<?php

namespace TechStudio\Blog\app\Providers;

use Illuminate\Support\ServiceProvider;
use TechStudio\Blog\app\Console\Commands\UpdateBlogViewsCount;

class BlogServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        // $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }

    public function register(): void
    {
        $this->commands(UpdateBlogViewsCount::class);
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
