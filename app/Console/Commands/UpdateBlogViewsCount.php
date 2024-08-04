<?php

namespace TechStudio\Blog\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use TechStudio\Blog\app\Models\Article;


class UpdateBlogViewsCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:views-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $articles = Article::query()->get();

        foreach ($articles as $article) {
            $cacheKey = 'articleViewCount:'.$article->slug;

            $views = Cache::get($cacheKey) ?? [];

            $article->increment('viewsCount', count($views));

            Cache::forget($cacheKey);
        }

    }
}
