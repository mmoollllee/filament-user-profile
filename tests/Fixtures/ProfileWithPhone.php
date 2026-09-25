<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Tests\Fixtures;

use Filament\Forms\Components\TextInput;
use Mmoollllee\FilamentUserProfile\Filament\Pages\EditProfile;

/**
 * An application's profile page with a field of its own.
 */
class ProfileWithPhone extends EditProfile
{
    protected function getProfileFormComponents(): array
    {
        return [
            ...parent::getProfileFormComponents(),
            TextInput::make('phone')->label('Handynummer')->tel()->required(),
        ];
    }
}
