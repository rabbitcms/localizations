<?php
declare(strict_types=1);
namespace RabbitCMS\Localizations;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator as RoutingUrlGenerator;

/**
 * Class UrlGenerator.
 */
class UrlGenerator extends RoutingUrlGenerator
{
    /**
     * @param Route $route
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     * @throws UrlGenerationException
     */
    public function toRoute($route, $parameters, $absolute)
    {
        $parameters = $this->formatParameters($parameters);

        $domain = $this->getRouteDomain($route, $parameters);
        $uri = $this->replaceRouteParameters($route->uri(), $parameters);

        $needs = (array)($route->getAction()['locale'] ?? false);
        $needs = end($needs);

        if (array_key_exists('locale', $parameters)) {
            $locale = $parameters['locale'];
            unset($parameters['locale']);
        } else {
            $locale = \Lang::getLocale();
        }

        if ($needs && $locale !== \Lang::getFallback()) {
            $uri = $locale . '/' . $uri;
        }
        $root = $this->replaceRoot($route, $domain, $parameters);
        $uri = $this->addQueryString($this->trimUrl($root, $uri), $parameters);

        if (preg_match('/\{.*?\}/', $uri)) {
            throw UrlGenerationException::forMissingParameters($route);
        }

        $uri = strtr(rawurlencode($uri), $this->dontEncode);

        return $absolute ? $uri : '/' . ltrim(str_replace($root, '', $uri), '/');
    }
}
