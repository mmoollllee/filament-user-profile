<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

/**
 * Every key the code asks for has to exist in every shipped locale — a
 * missing one does not fail anywhere in Laravel, `__()` just returns the key.
 * The keys are read out of the source, so a new string cannot be added
 * without its translations.
 *
 * @return array<int, string>
 */
function userProfileKeysUsedInSource(): array
{
    $keys = [];

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/src'));

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        preg_match_all("/UserProfile::trans\(\s*'([a-z0-9_.]+)'/i", (string) file_get_contents($file->getPathname()), $matches);

        $keys = [...$keys, ...$matches[1]];
    }

    return array_values(array_unique($keys));
}

it('finds the strings it is meant to check', function (): void {
    expect(userProfileKeysUsedInSource())->toContain('photo.label', 'tabs.profile');
});

it('has every used key in every locale', function (string $locale): void {
    $strings = require dirname(__DIR__, 2)."/lang/{$locale}/default.php";

    $missing = array_values(array_filter(
        userProfileKeysUsedInSource(),
        fn (string $key): bool => ! Arr::has($strings, $key),
    ));

    expect($missing)->toBe([]);
})->with(['de', 'en']);

it('keeps both locales structurally identical', function (): void {
    $de = Arr::dot(require dirname(__DIR__, 2).'/lang/de/default.php');
    $en = Arr::dot(require dirname(__DIR__, 2).'/lang/en/default.php');

    expect(array_keys($de))->toBe(array_keys($en));
});
