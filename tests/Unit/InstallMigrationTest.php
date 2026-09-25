<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

function photoMigration(): Migration
{
    return require dirname(__DIR__, 2).'/database/migrations/add_profile_photo_to_users_table.php.stub';
}

it('adds the configured photo column, once', function (): void {
    config(['user-profile.photo.column' => 'avatar_path']);

    photoMigration()->up();
    photoMigration()->up();

    expect(Schema::hasColumn('users', 'avatar_path'))->toBeTrue();

    photoMigration()->down();

    expect(Schema::hasColumn('users', 'avatar_path'))->toBeFalse();
});

it('leaves an existing photo column alone', function (): void {
    photoMigration()->up();

    expect(Schema::hasColumn('users', 'profile_photo_path'))->toBeTrue();
});
