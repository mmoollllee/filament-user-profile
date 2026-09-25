<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mmoollllee\FilamentUserProfile\AvatarProviders\InitialsAvatarProvider;
use Mmoollllee\FilamentUserProfile\UserProfile;

/**
 * Profile photo of a user model: its URL, Filament's avatar, and the file's
 * lifecycle — a replaced, removed or orphaned photo is deleted from the disk.
 *
 * @mixin Model
 */
trait InteractsWithProfilePhoto
{
    public static function bootInteractsWithProfilePhoto(): void
    {
        static::updated(function (self $user): void {
            $column = UserProfile::photoColumn();

            if ($user->wasChanged($column)) {
                $user->forgetPhotoFile($user->getOriginal($column));
            }
        });

        static::deleted(function (self $user): void {
            // A soft-deleted account may come back — with its photo.
            if (method_exists($user, 'isForceDeleting') && ! $user->isForceDeleting()) {
                return;
            }

            $user->forgetPhotoFile($user->getAttribute(UserProfile::photoColumn()));
        });
    }

    public function profilePhotoPath(): ?string
    {
        $path = $this->getAttribute(UserProfile::photoColumn());

        return is_string($path) && $path !== '' ? $path : null;
    }

    /**
     * Where the photo is handed out. The version changes with every new
     * photo, so browsers may keep it cached. Null without a photo.
     */
    public function profilePhotoUrl(): ?string
    {
        $path = $this->profilePhotoPath();

        if ($path === null) {
            return null;
        }

        return route('user-profile.photo', [
            'user' => $this->getKey(),
            'v' => substr(hash('sha256', $path), 0, 12),
        ]);
    }

    /**
     * Filament's avatar. Without a photo Filament falls back to the panel's
     * default avatar provider, e.g. {@see InitialsAvatarProvider}.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profilePhotoUrl();
    }

    public function deleteProfilePhoto(): void
    {
        if ($this->profilePhotoPath() === null) {
            return;
        }

        $this->forceFill([UserProfile::photoColumn() => null])->save();
    }

    /**
     * Deletes a photo file once the change is committed, so a rolled-back
     * save does not leave the account pointing at a missing file.
     */
    protected function forgetPhotoFile(mixed $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        $this->getConnection()->afterCommit(
            fn () => Storage::disk(UserProfile::photoDisk())->delete($path),
        );
    }
}
