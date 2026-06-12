<?php

use App\Models\Student_Organization;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

afterEach(function (): void {
    Mockery::close();
});

test('it returns default logo url when no logo exists', function (): void {
    $org = new Student_Organization;
    $org->logo = null;

    expect($org->logo_url)->toBe(asset('images/default-org-logo.svg'));
});

test('it uses public disk by default and returns url when logo exists', function (): void {
    Config::set('filesystems.default', 'local');

    $mockDisk = Mockery::mock();
    $mockDisk->shouldReceive('exists')
        ->once()
        ->with('logos/my-logo.png')
        ->andReturn(true);
    $mockDisk->shouldReceive('url')
        ->once()
        ->with('logos/my-logo.png')
        ->andReturn('/storage/logos/my-logo.png');

    Storage::shouldReceive('disk')
        ->once()
        ->with('public')
        ->andReturn($mockDisk);

    $org = new Student_Organization;
    $org->logo = 'logos/my-logo.png';

    expect($org->logo_url)->toBe('/storage/logos/my-logo.png');
});

test('it uses s3 disk and returns temporary url when default is s3', function (): void {
    Config::set('filesystems.default', 's3');

    $mockDisk = Mockery::mock();
    $mockDisk->shouldReceive('exists')
        ->once()
        ->with('logos/my-logo.png')
        ->andReturn(true);
    $mockDisk->shouldReceive('temporaryUrl')
        ->once()
        ->with('logos/my-logo.png', Mockery::any())
        ->andReturn('https://s3.amazonaws.com/logos/my-logo.png');

    Storage::shouldReceive('disk')
        ->once()
        ->with('s3')
        ->andReturn($mockDisk);

    $org = new Student_Organization;
    $org->logo = 'logos/my-logo.png';

    expect($org->logo_url)->toBe('https://s3.amazonaws.com/logos/my-logo.png');
});
