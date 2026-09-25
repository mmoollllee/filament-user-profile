<?php

declare(strict_types=1);

use Mmoollllee\FilamentUserProfile\Tests\TestCase;
use Mmoollllee\FilamentUserProfile\UserProfile;

uses(TestCase::class)->in('Unit');

uses()->beforeEach(function (): void {
    UserProfile::flush();
})->in('Unit');
