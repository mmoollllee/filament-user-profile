<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Mmoollllee\FilamentUserProfile\Tests\Fixtures\User;
use Mmoollllee\FilamentUserProfile\UserProfile;

beforeEach(function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('profile-photos/owner.jpg', 'jpeg-bytes');

    $this->owner = $this->user(['profile_photo_path' => 'profile-photos/owner.jpg']);
    $this->stranger = $this->user(['name' => 'Ben Fremd']);
});

it('hands people their own photo, unable to run as a document', function (): void {
    $this->actingAs($this->owner)
        ->get($this->owner->profilePhotoUrl())
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy')
        ->assertStreamedContent('jpeg-bytes');
});

it('keeps a photo from everybody else unless the application allows it', function (): void {
    $this->actingAs($this->stranger)
        ->get($this->owner->profilePhotoUrl())
        ->assertForbidden();

    UserProfile::authorizePhotoUsing(fn (User $viewer, User $owner): bool => $viewer->name === 'Ben Fremd');

    $this->actingAs($this->stranger)
        ->get($this->owner->profilePhotoUrl())
        ->assertOk();
});

it('lets nobody in without signing in', function (): void {
    $this->getJson($this->owner->profilePhotoUrl())->assertUnauthorized();
});

it('answers "not found" without a photo, a file or a person', function (): void {
    $this->actingAs($this->stranger)
        ->get(route('user-profile.photo', ['user' => $this->stranger->id]))
        ->assertNotFound();

    Storage::disk('local')->delete('profile-photos/owner.jpg');

    $this->actingAs($this->owner)
        ->get($this->owner->profilePhotoUrl())
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->get(route('user-profile.photo', ['user' => 999]))
        ->assertNotFound();
});
