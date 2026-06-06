<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

// pest()->extend(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function imagesMatch(
    string $actualPath,
    string $fixturePath,
    int $colorThreshold = 32,
    float $clusterThreshold = 0.005,
    int $minClusterNeighbors = 5,
): bool {
    // Cluster-based comparison so one fixture works across macOS/Linux.
    // FreeType produces scattered single-pixel diffs along glyph edges
    // that don't share neighbors. Real content changes produce contiguous
    // regions where most differing pixels have differing neighbors.
    $actual = imagecreatefrompng($actualPath);
    $expected = imagecreatefrompng($fixturePath);

    if ($actual === false || $expected === false) {
        return false;
    }

    if (imagesx($actual) !== imagesx($expected) || imagesy($actual) !== imagesy($expected)) {
        return false;
    }

    $width = imagesx($actual);
    $height = imagesy($actual);
    $mask = array_fill(0, $width * $height, 0);

    for ($y = 0; $y < $height; $y++) {
        $row = $y * $width;
        for ($x = 0; $x < $width; $x++) {
            $a = imagecolorat($actual, $x, $y);
            $b = imagecolorat($expected, $x, $y);

            if ($a === $b) {
                continue;
            }

            $redDiff = abs((($a >> 16) & 0xFF) - (($b >> 16) & 0xFF));
            $greenDiff = abs((($a >> 8) & 0xFF) - (($b >> 8) & 0xFF));
            $blueDiff = abs(($a & 0xFF) - ($b & 0xFF));

            if (max($redDiff, $greenDiff, $blueDiff) > $colorThreshold) {
                $mask[$row + $x] = 1;
            }
        }
    }

    $clustered = 0;
    for ($y = 1; $y < $height - 1; $y++) {
        $row = $y * $width;
        for ($x = 1; $x < $width - 1; $x++) {
            if (! $mask[$row + $x]) {
                continue;
            }

            $n = $mask[$row - $width + $x - 1]  // Top-left
                + $mask[$row - $width + $x]     // Top
                + $mask[$row - $width + $x + 1] // Top-right
                + $mask[$row + $x - 1]          // Left
                + $mask[$row + $x + 1]          // Right
                + $mask[$row + $width + $x - 1] // Bottom-left
                + $mask[$row + $width + $x]     // Bottom
                + $mask[$row + $width + $x + 1]; // Bottom-right

            if ($n >= $minClusterNeighbors) {
                $clustered++;
            }
        }
    }

    return ($clustered / ($width * $height)) <= $clusterThreshold;
}
