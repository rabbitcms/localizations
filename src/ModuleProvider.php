<?php

declare(strict_types=1);

namespace RabbitCMS\Localizations;

use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Class ModuleProvider.
 */
class ModuleProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->extend('url', function (UrlGenerator $generator) {
            return $generator->formatPathUsing(function ($path, ?Route $route) {
                if (is_null($route)) {
                    return $path;
                }
                $needs = (array) ($route->getAction()['locale'] ?? false);
                $needs = end($needs);
                if (! $needs) {
                    return $path;
                }
                $locale = Lang::getLocale();

                if ($locale !== Lang::getFallback()) {
                    return $locale.'/'.ltrim($path, '/');
                }

                return $path;
            });
        });
    }

    /**
     * @param  Kernel  $kernel
     */
    public function boot(Kernel $kernel)
    {
        if ($kernel instanceof HttpKernel) {
            $kernel->pushMiddleware(LocalizationMiddleware::class);
        }
    }
}
