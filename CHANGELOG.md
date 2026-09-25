# Changelog

All notable changes to `mmoollllee/filament-user-profile` will be documented in this file.

## 0.1.0 - 2026-09-25

Initial release.

- `Concerns\InteractsWithProfilePhoto` + `Contracts\HasProfilePhoto`: photo
  path and URL, Filament's avatar, and the file's lifecycle — a replaced,
  removed or orphaned photo is deleted once the change is committed.
- Photo route `user-profile.photo` on a private disk: the owner always, others
  as `UserProfile::authorizePhotoUsing()` allows; served with `nosniff` and a
  sandboxing content security policy; versioned URLs for long browser caching.
- `AvatarProviders\InitialsAvatarProvider`: initials on a colour derived from
  the name, drawn locally as an SVG data URI — for users and tenants.
- `Filament\Forms\Components\ProfilePhotoUpload`: round, croppable, scaled
  down in the browser, JPG/PNG/WebP only.
- `Filament\Pages\EditProfile`: "profile" and "sign-in" tabs, extended by
  applications through `getProfileFormComponents()` (next to the photo),
  `getProfileFooterComponents()` (below it) and `getExtraTabs()`.
- `UserProfilePlugin`: registers the page and the initials avatars on a panel;
  the page takes the simple layout in panels with tenancy unless told
  otherwise.
- Migration stub for the photo column that leaves an existing column alone.
- German and English translations.
