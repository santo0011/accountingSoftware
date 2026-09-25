<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    /** Read an application setting (Admin → Settings). */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (! function_exists('money')) {
    /** Format an amount in Indian style, e.g. ₹1,25,000.00 */
    function money(float|int|string|null $amount, bool $decimals = true): string
    {
        $amount = (float) $amount;
        $negative = $amount < 0;
        [$whole, $cents] = explode('.', number_format(abs($amount), 2, '.', ''));
        $fraction = $decimals ? '.'.$cents : '';

        if (strlen($whole) > 3) {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($whole, 0, -3));
            $whole = $rest.','.substr($whole, -3);
        }

        return ($negative ? '-' : '').setting('currency_symbol', '₹').$whole.$fraction;
    }
}

if (! function_exists('storage_asset')) {
    /** Public URL for an image stored on the public disk, with fallback. */
    function storage_asset(?string $path, ?string $fallback = null): ?string
    {
        return $path ? asset('storage/'.$path) : $fallback;
    }
}
