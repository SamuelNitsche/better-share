<?php

namespace SamuelNitsche\BetterShare;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class BetterShareServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            Commands\ShareCommand::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/better-share.php' => config_path('better-share.php'),
        ], 'config');

        $this->rewriteConfig();
    }

    protected function rewriteConfig()
    {
        if ($this->isSharing() === false) {
            return;
        }

        $publicUrl = file_get_contents(storage_path('sharefile'));
        URL::forceRootUrl($publicUrl);
        URL::forceScheme('https');
    }

    protected function isSharing(): bool
    {
        if (App::isLocal() === false) {
            return false;
        }

        if (! file_exists(storage_path('sharefile'))) {
            return false;
        }

        if (request()->hasHeader('X-Better-Share') === false) {
            return false;
        }

        return true;
    }
}
