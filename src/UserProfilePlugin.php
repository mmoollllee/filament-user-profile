<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Mmoollllee\FilamentUserProfile\AvatarProviders\InitialsAvatarProvider;
use Mmoollllee\FilamentUserProfile\Filament\Pages\EditProfile;

/**
 * Wires a panel: the profile page and locally drawn initials as the default
 * avatar.
 */
class UserProfilePlugin implements Plugin
{
    /**
     * @var class-string<BaseEditProfile>
     */
    protected string $page = EditProfile::class;

    protected bool $hasInitialsAvatars = true;

    /**
     * Null decides at boot: the simple layout for panels with tenancy, the
     * full one otherwise.
     */
    protected ?bool $isSimple = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'user-profile';
    }

    /**
     * The application's subclass of {@see EditProfile}, carrying its fields.
     *
     * @param  class-string<BaseEditProfile>  $page
     */
    public function page(string $page): static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Keep Filament's default avatar provider instead of the initials — e.g.
     * when the panel already sets one of its own.
     */
    public function initialsAvatars(bool $condition = true): static
    {
        $this->hasInitialsAvatars = $condition;

        return $this;
    }

    /**
     * The simple layout — a card without navigation — or the panel's full
     * one. Without a decision here, panels with tenancy get the simple
     * layout: the profile lives outside every tenant, where the full layout
     * cannot draw the tenant menu.
     */
    public function simple(bool $condition = true): static
    {
        $this->isSimple = $condition;

        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel->profile($this->page, isSimple: $this->isSimple ?? false);

        if ($this->hasInitialsAvatars) {
            $panel->defaultAvatarProvider(InitialsAvatarProvider::class);
        }
    }

    /**
     * Tenancy is known only once the whole panel is configured, which is why
     * the layout is decided here rather than in register().
     */
    public function boot(Panel $panel): void
    {
        if ($this->isSimple === null) {
            $panel->simpleProfilePage($panel->hasTenancy());
        }
    }
}
