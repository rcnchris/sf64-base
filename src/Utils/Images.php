<?php

namespace App\Utils;

use Intervention\Image\{Image, ImageManagerStatic};

final class Images
{
    public static function make(mixed $source = null): Image
    {
        $image = null;
        if (empty($source)) {
            $image = new Image();
        } elseif (is_string($source)) {
            $image = ImageManagerStatic::make($source);
        } elseif ($source instanceof Image) {
            $image = $source;
        }
        return $image;
    }

    public static function encode(mixed $source, ?string $format = 'data-url', ?int $quality = 90): string
    {
        return self::make($source)->encode($format, $quality)->getEncoded();
    }
}
