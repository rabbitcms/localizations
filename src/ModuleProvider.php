<?php
declare(strict_types = 1);
namespace RabbitCMS\Localizations;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
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
        $this->registerUrlGenerator();
    }

    /**
     * @param Kernel $kernel
     */
    public function boot(Kernel $kernel)
    {
        if ($kernel instanceof HttpKernel) {
            $kernel->pushMiddleware(LocalizationMiddleware::class);
        }
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app['url'] = $this->app->share(function (Application $app) {

            $routes = $app->make('router')->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            $url = new UrlGenerator($routes, $app->rebinding('request', $this->requestRebinder()));

            $url->setSessionResolver(function () {
                return $this->app->make('session');
            });

            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function (Application $app, $routes) {
                $app->make('url')->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    protected function requestRebinder()
    {
        return function (Application $app, $request) {
            $app->make('url')->setRequest($request);
        };
    }
}
