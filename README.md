# Filament User Profile

Profile photos and a profile page for Filament v5 — the "who is this?" half of
a user account.

- **Photos stay private.** Stored on a private disk and handed out only through
  an authorised route: to their owner, and to whoever your application allows
  (the people sharing a team with them, say). The upload refuses file paths
  the browser made up, and nothing outside the photo directory is ever handed
  out or deleted.
- **No third-party avatar service.** People without a photo get their initials
  on a colour derived from their name, drawn locally as an SVG — Filament's
  default asks ui-avatars.com, which receives every name shown in the panel.
  The same provider draws tenant avatars.
- **Small, round, croppable uploads.** Cropped to a square and scaled down in
  the browser before upload. JPG, PNG and WebP only: an SVG can carry script.
- **Old files go away.** A replaced or removed photo — or the one of a deleted
  account — is deleted from the disk once the change is committed. A
  rolled-back save keeps it.
- **Cache-friendly URLs.** Every photo URL carries a version that changes with
  the photo, so browsers keep it for a day and never show a stale picture.
- **A profile page to extend.** A "profile" tab with the photo next to your own
  fields, and a "sign-in" tab with Filament's e-mail and password fields, which
  ask for the current password before either changes.

## Installation

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/mmoollllee/filament-user-profile" }
]
```

```bash
composer require mmoollllee/filament-user-profile
php artisan vendor:publish --tag=user-profile-migrations
php artisan migrate
```

The migration adds `profile_photo_path` to the users table — unless a column of
the configured name already exists, which is the case for installs that had a
photo feature of their own.

## Wiring

### 1. The user model

```php
use Filament\Models\Contracts\HasAvatar;
use Mmoollllee\FilamentUserProfile\Concerns\InteractsWithProfilePhoto;
use Mmoollllee\FilamentUserProfile\Contracts\HasProfilePhoto;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasProfilePhoto
{
    use InteractsWithProfilePhoto;

    protected $fillable = [
        // …
        'profile_photo_path',
    ];
}
```

The profile page saves through `$user->update()`, so the photo column has to
be fillable.

### 2. The panel

```php
use Mmoollllee\FilamentUserProfile\UserProfilePlugin;

$panel->plugin(UserProfilePlugin::make());
```

That registers the profile page and makes the initials the default avatar.
`->page(AppProfile::class)` swaps in your own page (see below);
`->initialsAvatars(false)` keeps whatever avatar provider the panel sets.

The page uses the panel's full layout — except in panels with tenancy, where
it lives outside every tenant and the full layout cannot draw the tenant menu.
There it takes the simple layout, a card without navigation. `->simple()` and
`->simple(false)` decide it yourself.

### 3. Who sees whose photo

Everybody sees their own. Everything beyond that is your application's call,
registered at boot:

```php
use Mmoollllee\FilamentUserProfile\UserProfile;

UserProfile::authorizePhotoUsing(
    fn (User $viewer, User $owner): bool => $viewer->sharesTeamWith($owner),
);
```

Without a callback, a photo is visible to its owner only — the safe default,
because the route answers to any signed-in account.

## Your own profile fields

Subclass the page and add fields next to the photo, below it (across the full
width) or in more tabs:

```php
use Mmoollllee\FilamentUserProfile\Filament\Pages\EditProfile;

class AppProfile extends EditProfile
{
    protected function getProfileFormComponents(): array
    {
        return [
            TextInput::make('first_name')->required(),
            TextInput::make('last_name')->required(),
            TextInput::make('phone')->tel(),
        ];
    }

    protected function getProfileFooterComponents(): array
    {
        return [
            Grid::make(3)->schema([
                TextInput::make('postal_code'),
                TextInput::make('city'),
                TextInput::make('household_size')->integer(),
            ]),
        ];
    }

    protected function getExtraTabs(): array
    {
        return [
            Tab::make('Teams')->schema([/* … */]),
        ];
    }
}
```

`getProfilePhotoFormComponent()` returns the upload field, should you want to
move or configure it. The field is also usable on its own, e.g. in a users
resource:

```php
use Mmoollllee\FilamentUserProfile\Filament\Forms\Components\ProfilePhotoUpload;

ProfilePhotoUpload::make(),
```

## Configuration

```bash
php artisan vendor:publish --tag=user-profile-config
```

| Key | Default | |
|---|---|---|
| `user_model` | `null` | the model the photo route looks up — `null` uses the default guard's provider |
| `photo.column` | `profile_photo_path` | |
| `photo.disk` | `local` | keep it private: photos are served through the route, never by URL |
| `photo.directory` | `profile-photos` | |
| `photo.size` | `512` | edge length in pixels after cropping |
| `photo.max_kilobytes` | `5120` | upload limit before the browser scales the photo down |
| `photo.accepted_types` | JPG, PNG, WebP | adding `image/svg+xml` opens the door to script |
| `route.path` | `profile-photos/{user}` | |
| `route.middleware` | `['web', 'auth']` | |
| `route.max_age` | `86400` | seconds a browser may keep a photo |

Translations (German and English) publish with `--tag=user-profile-lang`.

## What it does not do

- **Account deletion.** What deleting an account means — archiving what the
  person owns, handing things over — is the application's business. The photo
  file is deleted with the account either way.
- **Admin editing of other people's photos.** Use `ProfilePhotoUpload` in your
  users resource; the lifecycle works the same there.

## Testing

```bash
composer test
composer analyse
composer format
```

## License

MIT — see [LICENSE.md](LICENSE.md).
