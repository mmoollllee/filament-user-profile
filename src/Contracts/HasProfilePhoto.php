<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Contracts;

use Mmoollllee\FilamentUserProfile\Concerns\InteractsWithProfilePhoto;

/**
 * A user with a profile photo. {@see InteractsWithProfilePhoto}
 * implements it.
 */
interface HasProfilePhoto
{
    public function profilePhotoPath(): ?string;

    /**
     * Where the photo is handed out, or null without a photo.
     */
    public function profilePhotoUrl(): ?string;

    public function deleteProfilePhoto(): void;
}
