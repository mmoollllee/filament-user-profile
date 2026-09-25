<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mmoollllee\FilamentUserProfile\Contracts\HasProfilePhoto;
use Mmoollllee\FilamentUserProfile\UserProfile;

/**
 * The photo upload: round, croppable, scaled down in the browser, stored
 * privately and previewed through the authorised photo route.
 */
class ProfilePhotoUpload extends FileUpload
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? UserProfile::photoColumn());
    }

    /**
     * Aligned like the photo — a centred circle gets a centred hint, down to
     * its wrapped lines.
     */
    public function getHint(): Schema
    {
        $alignment = $this->getAlignment();
        $alignment = $alignment instanceof Alignment ? $alignment : Alignment::tryFrom((string) $alignment);

        $textAlign = match ($alignment) {
            Alignment::Center => 'center',
            Alignment::End, Alignment::Right => 'end',
            default => null,
        };

        return Schema::make()
            ->components([
                Text::make(UserProfile::trans('photo.helper', [
                    'size' => (string) round(UserProfile::photoMaxKilobytes() / 1024, 1),
                ]))->extraAttributes($textAlign !== null ? ['style' => "text-align: {$textAlign}"] : []),
            ])
            ->alignment($alignment);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $size = (string) UserProfile::photoSize();

        $this
            ->label(UserProfile::trans('photo.label'))
            ->belowContent(fn (ProfilePhotoUpload $component): Schema => $component->getHint())
            ->avatar()
            ->imageEditor()
            ->circleCropper()
            ->automaticallyResizeImagesToWidth($size)
            ->automaticallyResizeImagesToHeight($size)
            // After avatar(), which allows image/* — and an SVG can carry script.
            ->acceptedFileTypes(UserProfile::photoAcceptedTypes())
            ->maxSize(UserProfile::photoMaxKilobytes())
            // The circle is 8rem once FilePond has started. Claiming that height
            // from the first paint keeps the field from growing during start-up,
            // which made Filament's resize observer re-measure FilePond within
            // the same frame ("ResizeObserver loop completed…").
            ->extraAttributes(['style' => 'min-height: 8rem'], merge: true)
            ->disk(UserProfile::photoDisk())
            ->directory(UserProfile::photoDirectory())
            ->visibility('private')
            ->getUploadedFileUsing(function (string $file, ?Model $record): ?array {
                $disk = Storage::disk(UserProfile::photoDisk());

                if (! $disk->exists($file)) {
                    return null;
                }

                return [
                    'name' => basename($file),
                    'size' => $disk->size($file),
                    'type' => $disk->mimeType($file),
                    'url' => $record instanceof HasProfilePhoto && $record->profilePhotoPath() === $file
                        ? $record->profilePhotoUrl()
                        : null,
                ];
            });
    }
}
