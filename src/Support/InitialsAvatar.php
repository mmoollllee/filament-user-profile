<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Support;

/**
 * A round-cornered-by-CSS square with initials on a colour derived from the
 * name — rendered right here as an SVG data URI, so no name ever leaves the
 * application for a third-party avatar service.
 */
final class InitialsAvatar
{
    /**
     * Dark enough for white initials, distinct enough to tell people apart.
     *
     * @var array<int, string>
     */
    private const BACKGROUNDS = [
        '#0f766e',
        '#1d4ed8',
        '#6d28d9',
        '#be185d',
        '#b45309',
        '#15803d',
        '#0e7490',
        '#4338ca',
        '#9f1239',
        '#374151',
    ];

    /**
     * First letters of the first and the last word: "Anna Maria Schmidt" →
     * "AS", "Foodpecker" → "F", nothing → "?".
     */
    public static function initials(string $name): string
    {
        $words = array_values(array_filter(
            preg_split('/\s+/u', trim($name)) ?: [],
            fn (string $word): bool => $word !== '',
        ));

        if ($words === []) {
            return '?';
        }

        $initials = mb_substr($words[0], 0, 1);

        if (count($words) > 1) {
            $initials .= mb_substr($words[count($words) - 1], 0, 1);
        }

        return mb_strtoupper($initials);
    }

    public static function background(string $name): string
    {
        return self::BACKGROUNDS[crc32(mb_strtolower(trim($name))) % count(self::BACKGROUNDS)];
    }

    public static function svg(string $name): string
    {
        $initials = htmlspecialchars(self::initials($name), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $background = self::background($name);

        return '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="'.$background.'"/>'
            .'<text x="32" y="32" dy="0.35em" fill="#ffffff" text-anchor="middle"'
            .' font-family="ui-sans-serif, system-ui, -apple-system, \'Segoe UI\', sans-serif"'
            .' font-size="26" font-weight="600">'.$initials.'</text>'
            .'</svg>';
    }

    public static function dataUri(string $name): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::svg($name));
    }
}
