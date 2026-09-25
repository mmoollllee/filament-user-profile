<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | The model whose photos the photo route hands out. Null uses the model of
    | the default guard's user provider.
    |
    */

    'user_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Profile photos
    |--------------------------------------------------------------------------
    |
    | Photos are stored privately and only ever handed out through the photo
    | route, after UserProfile::canViewPhoto() allowed it. The upload field
    | crops them to a square and scales them down in the browser, so only a
    | small file travels. Raster formats only: an SVG can carry script.
    |
    */

    'photo' => [
        'column' => 'profile_photo_path',
        'disk' => 'local',
        'directory' => 'profile-photos',
        'size' => 512,
        'max_kilobytes' => 5120,
        'accepted_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Photo route
    |--------------------------------------------------------------------------
    |
    | Every photo URL carries a version that changes with the photo, so the
    | browser may keep it for `max_age` seconds without ever showing a stale
    | picture.
    |
    */

    'route' => [
        'path' => 'profile-photos/{user}',
        'middleware' => ['web', 'auth'],
        'max_age' => 86400,
    ],

];
