<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mmoollllee\FilamentUserProfile\Filament\Pages\EditProfile;
use Mmoollllee\FilamentUserProfile\Tests\Fixtures\ProfileWithFooter;
use Mmoollllee\FilamentUserProfile\Tests\Fixtures\ProfileWithPhone;

beforeEach(function (): void {
    Storage::fake('local');

    $this->me = $this->user();
    $this->actingAs($this->me);

    Filament::setCurrentPanel('app');
});

it('shows the profile with the photo and the sign-in details', function (): void {
    Livewire::test(EditProfile::class)
        ->assertSee(['Profil', 'Zugangsdaten', 'Profilfoto', "JPG, PNG oder WebP, bis\u{00A0}5\u{00A0}MB."])
        ->assertSeeHtml('fi-fo-file-upload-avatar fi-align-center')
        ->assertSeeHtml('style="text-align: center"')
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('profile_photo_path')
        ->assertFormFieldExists('email');
});

it('stores an uploaded photo privately', function (): void {
    Livewire::test(EditProfile::class)
        ->fillForm(['profile_photo_path' => UploadedFile::fake()->image('ich.jpg', 600, 600)])
        ->call('save')
        ->assertHasNoFormErrors();

    $path = $this->me->fresh()->profile_photo_path;

    expect($path)->toStartWith('profile-photos/');
    Storage::disk('local')->assertExists($path);
});

it('refuses SVG files, which can carry script', function (): void {
    Livewire::test(EditProfile::class)
        ->fillForm(['profile_photo_path' => UploadedFile::fake()->create('ich.svg', 1, 'image/svg+xml')])
        ->call('save')
        ->assertHasFormErrors(['profile_photo_path']);

    expect($this->me->fresh()->profile_photo_path)->toBeNull();
});

it('refuses a photo path the browser made up', function (): void {
    Storage::disk('local')->put('invoices/2026-09.pdf', 'someone else\'s file');

    Livewire::test(EditProfile::class)
        ->set('data.profile_photo_path', ['tampered' => 'invoices/2026-09.pdf'])
        ->call('save')
        ->assertHasFormErrors(['profile_photo_path']);

    expect($this->me->fresh()->profile_photo_path)->toBeNull();
});

it('removes the photo and its file', function (): void {
    Storage::disk('local')->put('profile-photos/ich.jpg', 'jpeg-bytes');
    $this->me->update(['profile_photo_path' => 'profile-photos/ich.jpg']);

    Livewire::test(EditProfile::class)
        ->fillForm(['profile_photo_path' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->me->fresh()->profile_photo_path)->toBeNull();
    Storage::disk('local')->assertMissing('profile-photos/ich.jpg');
});

it('lets applications add their own fields', function (): void {
    Livewire::test(ProfileWithPhone::class)
        ->assertFormFieldExists('phone')
        ->fillForm(['phone' => '+49 171 1234567'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->me->fresh()->phone)->toBe('+49 171 1234567');
});

it('lets applications add fields below the photo', function (): void {
    Livewire::test(ProfileWithFooter::class)
        ->assertFormFieldExists('phone')
        ->fillForm(['phone' => '+49 30 1234567'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->me->fresh()->phone)->toBe('+49 30 1234567');
});
