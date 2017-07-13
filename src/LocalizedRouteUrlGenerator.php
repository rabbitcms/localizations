<?php
declare(strict_types=1);
namespace RabbitCMS\Localizations;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteUrlGenerator;
use Illuminate\Support\Facades\Lang;

/**
 * Class UrlGenerator.
 */
class LocalizedRouteUrlGenerator extends RouteUrlGenerator
{

    /**
     * @inheritdoc
     */
    public function to($route, $parameters = [], $absolute = false)
    {
        $domain = $this->getRouteDomain($route, $parameters);

        // First we will construct the entire URI including the root and query string. Once it
        // has been constructed, we'll make sure we don't have any missing parameters or we
        // will need to throw the exception to let the developers know one was not given.
        $uri = $this->addQueryString($this->url->format(
            $root = $this->replaceRootParameters($route, $domain, $parameters),
            $this->addLocationPath($route, $this->replaceRouteParameters($route->uri(), $parameters), $parameters)
        ), $parameters);

        if (preg_match('/\{.*?\}/', $uri)) {
            throw UrlGenerationException::forMissingParameters($route);
        }

        // Once we have ensured that there are no missing parameters in the URI we will encode
        // the URI and prepare it for returning to the developer. If the URI is supposed to
        // be absolute, we will return it as-is. Otherwise we will remove the URL's root.
        $uri = strtr(rawurlencode($uri), $this->dontEncode);

        if (! $absolute) {
            return '/'.ltrim(str_replace($root, '', $uri), '/');
        }

        return $uri;
    }


    /**
     * @param string $uri
     *
     * @return string
     */
    protected function addLocationPath(Route $route, string $uri, array &$parameters = [])
    {
        $needs = (array)($route->getAction()['locale'] ?? false);
        $needs = end($needs);

        if (array_key_exists('locale', $parameters)) {
            $locale = $parameters['locale'];
            unset($parameters['locale']);
        } else {
            $locale = Lang::getLocale();
        }

        if ($needs && $locale !== Lang::getFallback()) {
            $uri = $locale . '/' . $uri;
        }

        return $uri;
    }
}
