<?php
declare(strict_types=1);

namespace RabbitCMS\Localizations;

/**
 * @param string                 $format
 * @param int|\DateTimeInterface $time
 *
 * @return string
 */
function strftime(string $format, $time): string
{
    return StrFromTime::format($format,$time);
}