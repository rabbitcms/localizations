<?php
declare(strict_types=1);

namespace RabbitCMS\Localizations;

use Illuminate\Routing\UrlGenerator;

/**
 * Class LocalizedUrlGenerator
 *
 * @package RabbitCMS\Localizations
 */
class LocalizedUrlGenerator extends UrlGenerator
{
    /**
     * @inheritdoc
     */
    public function routeUrl()
    {
        if (! $this->routeGenerator) {
            $this->routeGenerator = new LocalizedRouteUrlGenerator($this, $this->request);
        }

        return $this->routeGenerator;
    }
}
