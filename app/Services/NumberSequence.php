<?php

namespace App\Services;

use App\Models\ShopSetting;
use Illuminate\Support\Facades\DB;

/**
 * Document numbers that "continue automatically" (requirements §3.2): prefix + zero-padded
 * counter kept in shop_settings, e.g. INV-2026-0047. The counter row is locked, so two
 * bills saved at the same time never get the same number.
 */
class NumberSequence
{
    public const INVOICE = ['invoice_prefix', 'invoice_next_no'];
    public const RECEIPT = ['receipt_prefix', 'receipt_next_no'];
    public const STOCK_ENTRY = ['stock_entry_prefix', 'stock_entry_next_no'];

    public static function next(array $keys, int $pad = 4): string
    {
        [$prefixKey, $counterKey] = $keys;

        return DB::transaction(function () use ($prefixKey, $counterKey, $pad) {
            ShopSetting::insertOrIgnore([
                'key' => $counterKey, 'value' => ShopSetting::$defaults[$counterKey] ?? '1', 'created_at' => now(), 'updated_at' => now(),
            ]);

            $counter = ShopSetting::where('key', $counterKey)->lockForUpdate()->first();
            $number = max(1, (int) $counter->value);
            $counter->update(['value' => (string) ($number + 1)]);

            // Read the prefix straight from the database (not the cache) while holding the lock.
            $prefix = ShopSetting::where('key', $prefixKey)->value('value') ?? ShopSetting::$defaults[$prefixKey] ?? '';

            return $prefix . str_pad((string) $number, $pad, '0', STR_PAD_LEFT);
        });
    }
}
