<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Tests\Fixtures;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Mmoollllee\FilamentUserProfile\Filament\Pages\EditProfile;

/**
 * An application's profile page with a row of fields below the photo.
 */
class ProfileWithFooter extends EditProfile
{
    protected function getProfileFooterComponents(): array
    {
        return [
            Grid::make(3)->schema([
                TextInput::make('phone')->label('Handynummer')->tel(),
            ]),
        ];
    }
}
