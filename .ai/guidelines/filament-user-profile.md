# Agent guidelines — `mmoollllee/filament-user-profile`

Profile photos and a profile page for Filament v5. If you are working in a
project that includes this package, prefer these rules over guesses from
similarly-named conventions.

## What belongs to the package, and what to the application

| Concern | Owner |
|---|---|
| Photo storage, URL, file lifecycle, the photo route | package (`InteractsWithProfilePhoto`, `ProfilePhotoController`) |
| Who may see whose photo beyond their own | **application**, via `UserProfile::authorizePhotoUsing()` |
| Default avatars for users and tenants | package (`InitialsAvatarProvider`) |
| The profile page's structure: tabs, photo, sign-in fields | package (`Filament\Pages\EditProfile`) |
| The person's own fields (names, phone, address, …) | **application** — a subclass overriding `getProfileFormComponents()` |
| Account deletion and what happens to a person's data | **application** |

## Rules of thumb

- **Link photos through `profilePhotoUrl()`, never through the disk.** The
  disk is private; the route checks who asks. A hand-built storage URL either
  does not work or leaks the photo.
- **The URL carries a version.** It changes with the photo, which is what
  makes the long `Cache-Control` safe. Do not strip the query string.
- **Keep the accepted types raster-only.** `FileUpload::avatar()` allows
  `image/*`, SVG included, and an SVG can carry script; `ProfilePhotoUpload`
  narrows it back after calling `avatar()`. Adding SVG to the configuration
  reopens that door.
- **The photo column only ever points into the photo directory.** The upload
  refuses paths the browser made up, and the route and the lifecycle ignore
  anything outside `photo.directory` (`UserProfile::isPhotoPath()`). To reuse a
  file stored elsewhere, copy it into the directory; do not loosen either check.
- **The photo column must be fillable.** The profile page saves through
  `$user->update()`.
- **Do not delete photo files by hand.** The trait deletes the old file after
  the change commits; deleting it earlier breaks the account when the save
  rolls back.
- **Everybody sees their own photo.** `authorizePhotoUsing()` only widens; a
  callback cannot hide a person's photo from themselves.
- **Every user-facing string goes through `UserProfile::trans()`** and into
  both `lang/de` and `lang/en`. `TranslationKeysTest` reads the keys out of the
  source.

## Anti-patterns to refuse

- Re-implementing a profile photo, a photo route or an initials avatar in an
  application that already has this package.
- Fetching default avatars from a third-party service. Every name in the panel
  would be sent to it.
- Serving photos from a public disk "for simplicity". Anybody with the URL
  would see them, signed in or not.
