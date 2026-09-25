<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Tests\Fixtures;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mmoollllee\FilamentUserProfile\Concerns\InteractsWithProfilePhoto;
use Mmoollllee\FilamentUserProfile\Contracts\HasProfilePhoto;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasProfilePhoto
{
    use InteractsWithProfilePhoto;

    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
