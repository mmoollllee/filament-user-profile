<?php

declare(strict_types=1);

use Mmoollllee\FilamentUserProfile\Support\InitialsAvatar;

it('takes the first letters of the first and the last word', function (string $name, string $initials): void {
    expect(InitialsAvatar::initials($name))->toBe($initials);
})->with([
    'full name' => ['Anna Maria Schmidt', 'AS'],
    'one word' => ['foodpecker', 'F'],
    'umlauts and spaces' => ['  özil   Mesut ', 'ÖM'],
    'nothing' => ['', '?'],
]);

it('gives the same name the same colour', function (): void {
    expect(InitialsAvatar::background('Anna Schmidt'))
        ->toBe(InitialsAvatar::background(' anna schmidt '))
        ->toMatch('/^#[0-9a-f]{6}$/');
});

it('escapes what it writes into the SVG', function (): void {
    expect(InitialsAvatar::svg('<script> alert'))
        ->toContain('&lt;A')
        ->not->toContain('<script');
});

it('renders a data URI Filament can show', function (): void {
    $uri = InitialsAvatar::dataUri('Anna Schmidt');

    expect($uri)->toStartWith('data:image/svg+xml;base64,')
        ->and(base64_decode(substr($uri, strlen('data:image/svg+xml;base64,'))))->toContain('>AS</text>');
});
