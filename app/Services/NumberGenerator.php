<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

/**
 * Generates gap-free, human-friendly document numbers such as APP-2026-000001.
 * The sequence row is locked so concurrent requests never receive the same number.
 */
class NumberGenerator
{
    private const DEFAULT_PREFIXES = [
        'application' => ['application_prefix', 'APP'],
        'invoice' => ['invoice_prefix', 'INV'],
        'payment' => ['payment_prefix', 'PAY'],
        'ticket' => ['ticket_prefix', 'TKT'],
        'customer' => ['customer_prefix', 'CUS'],
    ];

    public function __construct(private SettingService $settings) {}

    public function next(string $type): string
    {
        $year = (int) now()->format('Y');

        $number = DB::transaction(function () use ($type, $year) {
            NumberSequence::query()->insertOrIgnore([
                'type' => $type, 'year' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);

            $sequence = NumberSequence::where('type', $type)->where('year', $year)->lockForUpdate()->first();
            $sequence->increment('last_number');

            return $sequence->last_number;
        });

        [$settingKey, $fallback] = self::DEFAULT_PREFIXES[$type] ?? [null, strtoupper(substr($type, 0, 3))];
        $prefix = $settingKey ? $this->settings->get($settingKey, $fallback) : $fallback;

        return sprintf('%s-%d-%06d', $prefix, $year, $number);
    }
}
