<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use Mmoollllee\FilamentUserProfile\UserProfilePlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login()
            ->plugin(UserProfilePlugin::make());
    }
}
