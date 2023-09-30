<?php

namespace Lms\Course\Providers;

use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/UserRoute.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
