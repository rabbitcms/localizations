<?php
declare(strict_types=1);
namespace RabbitCMS\Localizations;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use ReflectionClass;

/**
 * Class LocalizationMiddleware.
 */
class LocalizationMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle(Request $request, Closure $next)
    {
        $locales = (array)\Config::get('app.locales', []);
        $segment = $request->segment(1);
        if (count($locales) && in_array($segment, $locales)) {
            $request->server->set('REQUEST_URI',preg_replace("/^\/{$segment}/",'',$request->server->get('REQUEST_URI')));
            $class = new ReflectionClass($request);
            $segments = $request->segments();
            App::setLocale($segment);
            $property = $class->getProperty('pathInfo');
            $property->setAccessible(true);
            $property->setValue($request, null);
            $property = $class->getProperty('requestUri');
            $property->setAccessible(true);
            $property->setValue($request, null);
        }
        return $next($request);
    }
}
