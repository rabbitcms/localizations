<?php
declare(strict_types=1);
namespace RabbitCMS\Localizations;

use Closure;
use Illuminate\Http\Request;
use ReflectionClass;

/**
 * Class LocalizationMiddleware.
 */
class LocalizationMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locales = (array)\Config::get('app.locales', []);
        $segment = $request->segment(1);
        if (count($locales) && in_array($segment, $locales)) {
            $class = new ReflectionClass($request);
            $segments = $request->segments();
            \Lang::setLocale(array_shift($segments));
            $property = $class->getProperty('pathInfo');
            $property->setAccessible(true);
            $property->setValue($request, '/' . implode('/', $segments));
        }
        return $next($request);
    }
}
