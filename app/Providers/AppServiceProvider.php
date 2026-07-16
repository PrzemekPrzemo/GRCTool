<?php

namespace App\Providers;

use App\Services\SystemAlertsService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;
use SocialiteProviders\Azure\AzureExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('prod', 'production', 'staging')) {
            URL::forceScheme('https');
        }
        Event::listen(SocialiteWasCalled::class, AzureExtendSocialite::class.'@handle');

        View::composer('layouts.app', function (ViewInstance $view): void {
            $user = auth()->user();
            $view->with('systemAlerts', $user ? app(SystemAlertsService::class)->forUser($user) : []);
        });
    }
}
