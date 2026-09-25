<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mmoollllee\FilamentUserProfile\Http\Controllers\ProfilePhotoController;

class UserProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/user-profile.php', 'user-profile');
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'user-profile');

        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/user-profile.php' => config_path('user-profile.php'),
            ], 'user-profile-config');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/user-profile'),
            ], 'user-profile-lang');

            $this->publishes([
                __DIR__.'/../database/migrations/add_profile_photo_to_users_table.php.stub' => database_path(
                    'migrations/'.date('Y_m_d_His').'_add_profile_photo_to_users_table.php',
                ),
            ], 'user-profile-migrations');

            $this->publishes([
                __DIR__.'/../.ai/guidelines/filament-user-profile.md' => base_path('.ai/guidelines/filament-user-profile.md'),
            ], 'user-profile-ai-guidelines');
        }
    }

    /**
     * Registered here rather than left to the application because
     * {@see Concerns\InteractsWithProfilePhoto::profilePhotoUrl()} builds its
     * URLs against this exact route name.
     */
    protected function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware((array) config('user-profile.route.middleware', ['web', 'auth']))
            ->get((string) config('user-profile.route.path', 'profile-photos/{user}'), ProfilePhotoController::class)
            ->name('user-profile.photo');
    }
}
