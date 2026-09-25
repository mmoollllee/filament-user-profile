<?php

declare(strict_types=1);

namespace Mmoollllee\FilamentUserProfile\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Mmoollllee\FilamentUserProfile\Filament\Forms\Components\ProfilePhotoUpload;
use Mmoollllee\FilamentUserProfile\UserProfile;

/**
 * The profile page: a "profile" tab with the photo next to the person's own
 * fields, and a "sign-in" tab with Filament's e-mail and password fields —
 * which already ask for the current password before either changes.
 *
 * Applications subclass it and override {@see getProfileFormComponents()}
 * for the fields next to the photo, {@see getProfileFooterComponents()} for
 * the ones below it, or {@see getExtraTabs()} for more tabs.
 */
class EditProfile extends BaseEditProfile
{
    /**
     * Wide enough for the photo next to two columns of fields — in the simple
     * layout, too.
     */
    protected Width|string|null $maxWidth = Width::FourExtraLarge;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(false)
            ->components([
                Tabs::make('profile')
                    ->persistTabInQueryString()
                    ->tabs([
                        $this->getProfileTab(),
                        $this->getCredentialsTab(),
                        ...$this->getExtraTabs(),
                    ]),
            ]);
    }

    protected function getProfileTab(): Tab
    {
        return Tab::make(UserProfile::trans('tabs.profile'))
            ->id('profile')
            ->icon(Heroicon::OutlinedUserCircle)
            ->schema([
                Flex::make([
                    Grid::make(2)
                        ->schema($this->getProfileFormComponents()),
                    // A fixed width: sized by its content, the card and the
                    // upload's own resize observer keep re-measuring each other.
                    Section::make([
                        $this->getProfilePhotoFormComponent(),
                    ])
                        ->grow(false)
                        ->extraAttributes(['style' => 'width: 16rem; max-width: 100%;']),
                ])->from('md'),
                ...$this->getProfileFooterComponents(),
            ]);
    }

    /**
     * The fields next to the photo. Override to add the application's own.
     *
     * @return array<int, Component>
     */
    protected function getProfileFormComponents(): array
    {
        return [
            $this->getNameFormComponent()->columnSpanFull(),
        ];
    }

    /**
     * Centred in its card; the label stays for screen readers only.
     */
    /**
     * Components across the full width, below the photo and the fields next
     * to it — e.g. a row of address fields. None by default.
     *
     * @return array<int, Component>
     */
    protected function getProfileFooterComponents(): array
    {
        return [];
    }

    protected function getProfilePhotoFormComponent(): ProfilePhotoUpload
    {
        return ProfilePhotoUpload::make()
            ->hiddenLabel()
            ->alignCenter();
    }

    protected function getCredentialsTab(): Tab
    {
        return Tab::make(UserProfile::trans('tabs.credentials'))
            ->id('credentials')
            ->icon(Heroicon::OutlinedKey)
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    /**
     * More tabs after "sign-in", e.g. the teams a person belongs to.
     *
     * @return array<int, Tab>
     */
    protected function getExtraTabs(): array
    {
        return [];
    }
}
