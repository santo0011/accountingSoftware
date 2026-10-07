<?php

namespace App\Support;

/**
 * Allowed upload types in one place, used by validation rules and by the
 * file pickers' accept="" lists. Laravel's `mimes` rule checks the real file
 * content, so a .jfif (a JPEG inside) passes as jpg.
 */
final class FileTypes
{
    /** Images every browser can display: website pictures (service cards, banners, logo, hero). */
    public const WEB_IMAGES = ['jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'webp', 'gif', 'avif', 'bmp'];

    /** Photo formats accepted as documents too (downloaded rather than shown, e.g. iPhone HEIC). */
    public const PHOTOS = [...self::WEB_IMAGES, 'heic', 'heif', 'tif', 'tiff'];

    /** Application documents and support attachments. */
    public const DOCUMENTS = ['pdf', 'doc', 'docx', ...self::PHOTOS];

    /** Validation rule, e.g. FileTypes::rule(FileTypes::WEB_IMAGES) → "mimes:jpg,jpeg,…". */
    public static function rule(array $types): string
    {
        return 'mimes:'.implode(',', $types);
    }

    /** accept="" value for a file input, e.g. ".jpg,.jpeg,…" plus image/* when images are allowed. */
    public static function accept(array $types): string
    {
        $list = array_map(fn ($t) => '.'.$t, $types);
        if (array_intersect($types, self::WEB_IMAGES)) {
            $list[] = 'image/jpeg';
            $list[] = 'image/png';
            $list[] = 'image/webp';
        }

        return implode(',', $list);
    }
}
