<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * The one place the package asks the application "whose photo may who see,
 * and where do photos live?".
 *
 * Everything here is either read from `config/user-profile.php` or registered
 * at boot — there is no other coupling between the package and its host.
 */
class UserProfile
{
    /**
     * Decides whether a person may see someone else's photo. Receives the
     * viewer and the photo's owner.
     */
    protected static ?Closure $photoAuthorizer = null;

    /**
     * Widens who may see a photo, e.g. to everybody sharing a team with its
     * owner. Everybody sees their own photo either way; without a callback
     * that is all anybody sees.
     */
    public static function authorizePhotoUsing(?Closure $callback): void
    {
        static::$photoAuthorizer = $callback;
    }

    public static function canViewPhoto(Authenticatable $viewer, Model $owner): bool
    {
        if ($viewer instanceof Model && $viewer->is($owner)) {
            return true;
        }

        return static::$photoAuthorizer !== null && (bool) (static::$photoAuthorizer)($viewer, $owner);
    }

    /**
     * @return class-string<Model>
     */
    public static function userModel(): string
    {
        $provider = config('auth.guards.'.config('auth.defaults.guard').'.provider');

        /** @var class-string<Model> $model */
        $model = config('user-profile.user_model') ?? config("auth.providers.{$provider}.model");

        return $model;
    }

    public static function photoColumn(): string
    {
        return (string) config('user-profile.photo.column', 'profile_photo_path');
    }

    public static function photoDisk(): string
    {
        return (string) config('user-profile.photo.disk', 'local');
    }

    public static function photoDirectory(): string
    {
        return (string) config('user-profile.photo.directory', 'profile-photos');
    }

    /**
     * Whether a path names a file directly inside the photo directory — the
     * only files the photo route hands out and the photo lifecycle deletes.
     * The disk may hold other files, and the photo column is filled from a
     * form field whose value the browser controls.
     */
    public static function isPhotoPath(string $path): bool
    {
        $directory = trim(static::photoDirectory(), '/');
        $prefix = $directory === '' ? '' : preg_quote($directory, '#').'/';

        return preg_match('#\A'.$prefix.'[^/\\\\.][^/\\\\]*\z#', $path) === 1;
    }

    /**
     * Edge length in pixels a photo is cropped and scaled to before upload.
     */
    public static function photoSize(): int
    {
        return (int) config('user-profile.photo.size', 512);
    }

    public static function photoMaxKilobytes(): int
    {
        return (int) config('user-profile.photo.max_kilobytes', 5120);
    }

    /**
     * @return array<int, string>
     */
    public static function photoAcceptedTypes(): array
    {
        return array_values((array) config('user-profile.photo.accepted_types', ['image/jpeg', 'image/png', 'image/webp']));
    }

    /**
     * Seconds a browser may keep a photo — safe, because its URL changes
     * with every new photo.
     */
    public static function photoMaxAge(): int
    {
        return (int) config('user-profile.route.max_age', 86400);
    }

    /**
     * @param  array<string, mixed>  $replace
     */
    public static function trans(string $key, array $replace = []): string
    {
        return (string) __('user-profile::default.'.$key, $replace);
    }

    /**
     * Forgets everything registered at boot. For tests.
     */
    public static function flush(): void
    {
        static::$photoAuthorizer = null;
    }
}
