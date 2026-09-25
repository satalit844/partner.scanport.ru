<?php
/**
 * Generates the PNG icon set used by the Training PWA rollout.
 *
 * Usage:
 * php generate-training-pwa-icons.php /absolute/path/to/assets/components/training/pwa/icons
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(1);
}

if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng')) {
    fwrite(STDERR, "GD extension is required.\n");
    exit(1);
}

$directory = isset($argv[1]) ? rtrim((string) $argv[1], '/\\') : '';

if ($directory === '') {
    fwrite(STDERR, "Target directory is required.\n");
    exit(1);
}

if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
    fwrite(STDERR, "Cannot create target directory.\n");
    exit(1);
}

$makeIcon = function ($path, $size) {
    $image = imagecreatetruecolor($size, $size);

    if (!$image) {
        return false;
    }

    $background = imagecolorallocate($image, 48, 27, 81);
    $violet = imagecolorallocate($image, 163, 95, 205);
    $white = imagecolorallocate($image, 255, 255, 255);
    $light = imagecolorallocate($image, 232, 214, 255);

    imagefilledrectangle($image, 0, 0, $size, $size, $background);

    $padding = (int) round($size * 0.18);
    $line = max(5, (int) round($size * 0.045));
    $middle = (int) round($size * 0.50);

    imagefilledrectangle($image, $padding, $middle - $line * 3, $size - $padding, $middle - $line * 2, $light);
    imagefilledrectangle($image, $padding, $middle - $line, $size - $padding, $middle, $white);
    imagefilledrectangle($image, $padding, $middle + $line * 2, $size - $padding, $middle + $line * 3, $light);

    $scanX = (int) round($size * 0.50);
    $scanWidth = max(8, (int) round($size * 0.075));

    imagefilledrectangle($image, $scanX - $scanWidth, $padding, $scanX + $scanWidth, $size - $padding, $violet);
    imagefilledrectangle(
        $image,
        $scanX - max(2, (int) round($scanWidth / 4)),
        $padding,
        $scanX + max(2, (int) round($scanWidth / 4)),
        $size - $padding,
        $white
    );

    $tmp = tempnam(dirname($path), '.icon-');

    if ($tmp === false || !imagepng($image, $tmp, 8)) {
        imagedestroy($image);
        if ($tmp) {
            @unlink($tmp);
        }
        return false;
    }

    imagedestroy($image);
    @chmod($tmp, 0644);

    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }

    $info = @getimagesize($path);
    return $info && (int) $info[0] === $size && (int) $info[1] === $size;
};

$files = array(
    180 => $directory . '/scanport-training-180-v2.png',
    192 => $directory . '/scanport-training-192-v2.png',
    512 => $directory . '/scanport-training-512-v2.png',
);

foreach ($files as $size => $path) {
    if (!$makeIcon($path, $size)) {
        fwrite(STDERR, "Could not generate {$path}.\n");
        exit(1);
    }

    echo basename($path) . ' ' . hash_file('sha256', $path) . PHP_EOL;
}
