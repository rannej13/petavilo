<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Facades\Storage;

final class SiteImages
{
    public static function path(string $name): string
    {
        return 'site/'.$name.'.jpg';
    }

    public static function url(string $name): ?string
    {
        $path = self::path($name);

        return Storage::disk('public')->exists($path) ? asset('storage/'.$path) : null;
    }

    public static function toJpeg(string $bytes): ?string
    {
        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 1600;

        if ($width > $maxWidth) {
            $scaled = imagescale($image, $maxWidth, (int) round($height * $maxWidth / $width));

            if ($scaled !== false) {
                imagedestroy($image);
                $image = $scaled;
            }
        }

        ob_start();
        imagejpeg($image, null, 82);
        imagedestroy($image);

        return (string) ob_get_clean();
    }
}
