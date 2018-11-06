<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\{User, Category, Link};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
	{
		\App\Models\User::observe(\App\Observers\UserObserver::class);
		\App\Models\Reply::observe(\App\Observers\ReplyObserver::class);
		\App\Models\Topic::observe(\App\Observers\TopicObserver::class);
        \App\Models\Link::observe(\App\Observers\LinkObserver::class);

        // Carbon 中文化配置
        \Carbon\Carbon::setLocale('zh');

        \View::share([
            'categories' => Category::all(),
            'active_users' => app(User::class)->getActiveUsers(),
            'links' => app(Link::class)->getAllCached(),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (app()->isLocal()) {
            $this->app->register(\VIACreative\SudoSu\ServiceProvider::class);
        }
    }
}
