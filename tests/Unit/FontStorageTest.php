<?php

use Yilanboy\Preview\Exceptions\RenderFailure;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\FontStorage;

afterEach(function () {
    FontStorage::reset();
});

it('defaults to system temp directory for storage path', function () {
    expect(FontStorage::getStoragePath())->toBe(sys_get_temp_dir().'/yilanboy-preview/fonts');
});

it('allows setting a custom storage path', function () {
    FontStorage::setStoragePath('/tmp/custom-font-storage');
    expect(FontStorage::getStoragePath())->toBe('/tmp/custom-font-storage');

    // Strips trailing slashes
    FontStorage::setStoragePath('/tmp/custom-font-storage/');
    expect(FontStorage::getStoragePath())->toBe('/tmp/custom-font-storage');
});

it('defaults to jsdelivr CDN for base URL', function () {
    expect(FontStorage::getBaseUrl())->toBe('https://cdn.jsdelivr.net/gh/yilanboy/preview@main/fonts');
});

it('allows setting a custom base URL', function () {
    FontStorage::setBaseUrl('https://my-cdn.example.com/fonts');
    expect(FontStorage::getBaseUrl())->toBe('https://my-cdn.example.com/fonts');

    // Strips trailing slashes
    FontStorage::setBaseUrl('https://my-cdn.example.com/fonts/');
    expect(FontStorage::getBaseUrl())->toBe('https://my-cdn.example.com/fonts');
});

it('returns cached font without downloading when file exists', function () {
    $tempDir = sys_get_temp_dir().'/preview-cache-test-'.bin2hex(random_bytes(4));
    mkdir($tempDir, 0777, true);

    $fontFile = $tempDir.'/inter.ttf';
    file_put_contents($fontFile, 'cached-font');

    FontStorage::setStoragePath($tempDir);
    // Point base url to invalid host to prove it doesn't try to download
    FontStorage::setBaseUrl('http://invalid.nonexistent.domain');

    expect(FontStorage::resolve(Font::Inter))->toBe($fontFile);

    unlink($fontFile);
    rmdir($tempDir);
});

it('downloads and caches a font successfully', function () {
    $remoteDir = sys_get_temp_dir().'/preview-remote-mock-'.bin2hex(random_bytes(4));
    $storageDir = sys_get_temp_dir().'/preview-storage-mock-'.bin2hex(random_bytes(4));
    mkdir($remoteDir, 0777, true);

    // Create a mock valid TTF in the remote directory
    $mockRemoteFont = $remoteDir.'/inter.ttf';
    file_put_contents($mockRemoteFont, hex2bin('00010000').'valid-font-payload');

    FontStorage::setStoragePath($storageDir);
    FontStorage::setBaseUrl($remoteDir);

    $resolved = FontStorage::resolve(Font::Inter);

    expect($resolved)->toBe($storageDir.'/inter.ttf')
        ->and(file_exists($resolved))->toBeTrue()
        ->and(file_get_contents($resolved))->toBe(hex2bin('00010000').'valid-font-payload');

    unlink($mockRemoteFont);
    rmdir($remoteDir);
    unlink($resolved);
    rmdir($storageDir);
});

it('throws RenderFailure when download source fails', function () {
    $storageDir = sys_get_temp_dir().'/preview-storage-fail-'.bin2hex(random_bytes(4));
    FontStorage::setStoragePath($storageDir);
    FontStorage::setBaseUrl('/nonexistent/path');

    expect(fn () => FontStorage::resolve(Font::Inter))
        ->toThrow(RenderFailure::class, 'Failed to download font [inter.ttf]');

    if (is_dir($storageDir)) {
        rmdir($storageDir);
    }
});

it('throws RenderFailure and cleans up temporary file when downloaded file is not valid TTF', function () {
    $remoteDir = sys_get_temp_dir().'/preview-remote-invalid-'.bin2hex(random_bytes(4));
    $storageDir = sys_get_temp_dir().'/preview-storage-invalid-'.bin2hex(random_bytes(4));
    mkdir($remoteDir, 0777, true);

    // Not a valid TTF header (e.g. 404 HTML body)
    $mockRemoteFont = $remoteDir.'/inter.ttf';
    file_put_contents($mockRemoteFont, '<html>404 Not Found</html>');

    FontStorage::setStoragePath($storageDir);
    FontStorage::setBaseUrl($remoteDir);

    expect(fn () => FontStorage::resolve(Font::Inter))
        ->toThrow(RenderFailure::class, 'is not a valid TrueType font')
        ->and(file_exists($storageDir.'/inter.ttf'))->toBeFalse()
        ->and(glob($storageDir.'/*.tmp.*'))->toBeEmpty();

    unlink($mockRemoteFont);
    rmdir($remoteDir);
    rmdir($storageDir);
});
