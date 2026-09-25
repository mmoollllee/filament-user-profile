<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Mmoollllee\FilamentUserProfile\AvatarProviders\InitialsAvatarProvider;
use Mmoollllee\FilamentUserProfile\Filament\Pages\EditProfile;
use Mmoollllee\FilamentUserProfile\Tests\Fixtures\Team;
use Mmoollllee\FilamentUserProfile\UserProfilePlugin;

it('wires the profile page into the full layout and draws initials as avatars', function (): void {
    $panel = Filament::getPanel('app');

    expect($panel->getProfilePage())->toBe(EditProfile::class)
        ->and($panel->isProfilePageSimple())->toBeFalse()
        ->and($panel->getDefaultAvatarProvider())->toBe(InitialsAvatarProvider::class);
});

it('uses the simple layout in panels with tenancy, where the full one has no tenant', function (): void {
    $panel = Panel::make()->id('teams')->path('teams')->tenant(Team::class);
    $plugin = UserProfilePlugin::make();

    $plugin->register($panel);
    $plugin->boot($panel);

    expect($panel->isProfilePageSimple())->toBeTrue();

    $forced = Panel::make()->id('forced')->path('forced')->tenant(Team::class);
    $plugin = UserProfilePlugin::make()->simple(false);

    $plugin->register($forced);
    $plugin->boot($forced);

    expect($forced->isProfilePageSimple())->toBeFalse();
});

it('shows initials instead of asking a third-party service', function (): void {
    Filament::setCurrentPanel('app');

    expect(Filament::getUserAvatarUrl($this->user()))
        ->toStartWith('data:image/svg+xml;base64,')
        ->not->toContain('ui-avatars');
});

it('shows the photo once there is one', function (): void {
    Filament::setCurrentPanel('app');
    $user = $this->user(['profile_photo_path' => 'profile-photos/anna.jpg']);

    expect(Filament::getUserAvatarUrl($user))->toContain("/profile-photos/{$user->id}?v=");
});
