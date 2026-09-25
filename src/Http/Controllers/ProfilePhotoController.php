<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mmoollllee\FilamentUserProfile\UserProfile;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Hands out a profile photo from the private disk — to its owner, and to
 * whoever the application allows via UserProfile::authorizePhotoUsing().
 */
class ProfilePhotoController
{
    public function __invoke(Request $request, string $user): StreamedResponse
    {
        $viewer = $request->user();

        abort_if($viewer === null, 403);

        $model = UserProfile::userModel();
        $owner = $model::query()->find($user);

        abort_if($owner === null, 404);
        abort_unless(UserProfile::canViewPhoto($viewer, $owner), 403);

        $path = $owner->getAttribute(UserProfile::photoColumn());

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(UserProfile::photoDisk());

        abort_unless(is_string($path) && $path !== '' && $disk->exists($path), 404);

        return $disk->response($path, headers: [
            'Cache-Control' => 'private, max-age='.UserProfile::photoMaxAge(),
            // Opened on its own, a photo must never run as a document.
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
