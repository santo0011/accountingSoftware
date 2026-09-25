<?php

namespace App\Support;

/**
 * Minimal HTML sanitiser for rich text entered in the admin panel
 * (service descriptions, CMS pages). Keeps formatting tags, strips scripts,
 * event handlers, inline styles and javascript: URLs.
 */
class Html
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><blockquote><table><thead><tbody><tr><th><td><hr><span>';

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html);
        $html = strip_tags($html, self::ALLOWED_TAGS);
        // Remove event handlers and inline styles.
        $html = preg_replace('/\s+(on\w+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        // Neutralise javascript:/data: URLs in links.
        $html = preg_replace('/href\s*=\s*(["\']?)\s*(javascript|data|vbscript):[^"\'>\s]*\1/i', 'href="#"', $html);

        return trim($html);
    }
}
