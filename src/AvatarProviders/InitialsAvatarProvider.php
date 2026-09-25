<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mmoollllee\FilamentUserProfile\Support\InitialsAvatar;

/**
 * Filament's default avatar — for users without a photo and for tenants —
 * drawn locally instead of fetched from ui-avatars.com, which would receive
 * every name shown in the panel.
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        return InitialsAvatar::dataUri(Filament::getNameForDefaultAvatar($record));
    }
}
