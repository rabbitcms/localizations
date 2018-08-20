<?php
declare(strict_types=1);

namespace RabbitCMS\Localizations;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class StrFromTime
 * @package RabbitCMS\Localizations
 */
class StrFromTime
{
    /**
     * A translator to ... er ... translate stuff.
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected static $translator;

    /**
     * Initialize the translator instance if necessary.
     *
     * @return \Symfony\Component\Translation\TranslatorInterface
     */
    protected static function translator()
    {
        if (static::$translator === null) {
            static::$translator = new Translator('en');
            static::$translator->addLoader('php', new PhpFileLoader());
            static::setLocale('en');
        }

        return static::$translator;
    }

    /**
     * Get the translator instance in use
     *
     * @return \Symfony\Component\Translation\TranslatorInterface
     */
    public static function getTranslator()
    {
        return static::translator();
    }

    /**
     * Set the translator instance to use
     *
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public static function setTranslator(TranslatorInterface $translator)
    {
        static::$translator = $translator;
    }

    /**
     * Get the current translator locale
     *
     * @return string
     */
    public static function getLocale()
    {
        return static::translator()->getLocale();
    }

    /**
     * Set the current translator locale and indicate if the source locale file exists
     *
     * @param string $locale
     *
     * @return bool
     */
    public static function setLocale($locale)
    {
        $locale = preg_replace_callback('/\b([a-z]{2})[-_](?:([a-z]{4})[-_])?([a-z]{2})\b/', function ($matches) {
            return $matches[1] . '_' . (!empty($matches[2]) ? ucfirst($matches[2]) . '_' : '') . strtoupper($matches[3]);
        }, strtolower($locale));

        static::translator()->setLocale($locale);

        if (file_exists($filename = dirname(__DIR__) . '/Lang/' . $locale . '-nominative.php')) {

            // Ensure the locale has been loaded.
            static::translator()->addResource('php', $filename, $locale, 'nominative');
        }

        if (file_exists($filename = dirname(__DIR__) . '/Lang/' . $locale . '-genitive.php')) {

            // Ensure the locale has been loaded.
            static::translator()->addResource('php', $filename, $locale, 'genitive');

        }

        return false;
    }

    /**
     * @param string                 $format
     * @param int|\DateTimeInterface $time
     * @param string|null            $locale
     *
     * @return string
     */
    public static function format(string $format, $time = null, string $locale = null): string
    {
        if ($time === null) {
            $time = new \DateTime();
        }

        if ($time instanceof \DateTimeInterface) {
            $time = $time->getTimestamp();
        }

        $locale = $locale ?? App::getLocale();
        static::setLocale($locale);

        try {
            $locale = setlocale(LC_TIME, 0);
            setlocale(LC_TIME, implode('.', explode('.', $locale, 2) + [null, 'UTF-8']));
            return preg_replace_callback(
                '#(?<!%)((?:%%)*)%(?<char>.)(?:\{(?<params>[^}]*)\})?#',
                function (array $matches) use ($time) {
                    switch ($matches['char']) {
                        case 'B':
                            if (array_key_exists('params', $matches)) {
                                $params = $matches['params'];
                                $value = Str::lower(static::getTranslator()->trans(date('F', $time), [],
                                    strpos($params, 'G') === false ? 'nominative' : 'genitive'));

                                if (strpos($params, 'U') !== false) {
                                    $value = Str::ucfirst($value);
                                }
                            } else {
                                $value = \strftime("%{$matches['char']}", $time);
                            }

                            return $matches[1] . $value;
                        default:
                            return $matches[1] . \strftime("%{$matches['char']}", $time);
                    }
                },
                $format
            );
        } finally {
            setlocale(LC_TIME, $locale);
        }
    }
}