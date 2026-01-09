<?php

declare(strict_types=1);

namespace SharpAPI\HrRelatedSkills;

use Illuminate\Support\ServiceProvider;

/**
 * @api
 */
class HrRelatedSkillsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sharpapi-hr-related-skills.php' => config_path('sharpapi-hr-related-skills.php'),
            ], 'sharpapi-hr-related-skills');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Merge the package configuration with the app configuration.
        $this->mergeConfigFrom(
            __DIR__.'/../config/sharpapi-hr-related-skills.php', 'sharpapi-hr-related-skills'
        );
    }
}