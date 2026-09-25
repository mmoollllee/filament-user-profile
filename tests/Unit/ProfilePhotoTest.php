<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('profile-photos/old.jpg', 'old');
    Storage::disk('local')->put('profile-photos/new.jpg', 'new');
});

it('has no photo URL without a photo', function (): void {
    $user = $this->user();

    expect($user->profilePhotoPath())->toBeNull()
        ->and($user->profilePhotoUrl())->toBeNull()
        ->and($user->getFilamentAvatarUrl())->toBeNull();
});

it('changes the photo URL with the photo, so caches never show an old one', function (): void {
    $user = $this->user(['profile_photo_path' => 'profile-photos/old.jpg']);
    $before = $user->profilePhotoUrl();

    $user->update(['profile_photo_path' => 'profile-photos/new.jpg']);

    expect($before)->toContain("/profile-photos/{$user->id}?v=")
        ->and($user->profilePhotoUrl())->not->toBe($before)
        ->and($user->profilePhotoUrl())->not->toContain('new.jpg');
});

it('deletes a replaced photo', function (): void {
    $user = $this->user(['profile_photo_path' => 'profile-photos/old.jpg']);

    $user->update(['profile_photo_path' => 'profile-photos/new.jpg']);

    Storage::disk('local')->assertMissing('profile-photos/old.jpg');
    Storage::disk('local')->assertExists('profile-photos/new.jpg');
});

it('keeps the photo when the change is rolled back', function (): void {
    $user = $this->user(['profile_photo_path' => 'profile-photos/old.jpg']);

    try {
        DB::transaction(function () use ($user): void {
            $user->update(['profile_photo_path' => 'profile-photos/new.jpg']);

            throw new RuntimeException('Something else failed.');
        });
    } catch (RuntimeException) {
        //
    }

    Storage::disk('local')->assertExists('profile-photos/old.jpg');
});

it('never deletes a file outside the photo directory', function (): void {
    Storage::disk('local')->put('invoices/2026-09.pdf', 'someone else\'s file');
    Storage::disk('local')->put('invoices/2026-10.pdf', 'someone else\'s file');
    $replaced = $this->user(['profile_photo_path' => 'invoices/2026-09.pdf']);
    $deleted = $this->user(['profile_photo_path' => 'invoices/2026-10.pdf']);

    $replaced->update(['profile_photo_path' => 'profile-photos/new.jpg']);
    $deleted->delete();

    Storage::disk('local')->assertExists('invoices/2026-09.pdf');
    Storage::disk('local')->assertExists('invoices/2026-10.pdf');
});

it('removes the photo on request and with the account', function (): void {
    $user = $this->user(['profile_photo_path' => 'profile-photos/old.jpg']);

    $user->deleteProfilePhoto();

    expect($user->fresh()->profile_photo_path)->toBeNull();
    Storage::disk('local')->assertMissing('profile-photos/old.jpg');

    $other = $this->user(['profile_photo_path' => 'profile-photos/new.jpg']);
    $other->delete();

    Storage::disk('local')->assertMissing('profile-photos/new.jpg');
});
