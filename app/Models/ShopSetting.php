<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * One row per setting (requirements §3.2). Read with ShopSetting::get(), write with put().
 */
class ShopSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Keys managed on the settings screen, with defaults used until saved. */
    public static array $defaults = [
        'shop_name' => 'My Shop',
        'address' => '',
        'phone' => '',
        'gstin' => '',
        'logo' => '',
        'invoice_prefix' => 'INV-2026-',
        'invoice_next_no' => '1',
        'receipt_prefix' => 'RCP-',
        'receipt_next_no' => '1',
        'stock_entry_prefix' => 'SIN-',
        'stock_entry_next_no' => '1',
        'print_size' => 'thermal_80mm',
        'bill_rounding' => 'nearest_rupee',
        'allow_negative_stock' => '0',
    ];

    public static array $printSizes = ['thermal_80mm' => 'Thermal 80 mm', 'a4' => 'A4'];

    public static array $roundings = ['nearest_rupee' => 'Nearest ₹1', 'exact' => 'Exact paise'];

    public static function get(string $key, $default = null)
    {
        // Counters change on every bill; always read them from the database, never the cache.
        if (static::isCounter($key)) {
            return static::where('key', $key)->value('value') ?? $default ?? (static::$defaults[$key] ?? null);
        }

        $all = Cache::rememberForever('shop_settings', fn () => static::pluck('value', 'key')->all());

        return $all[$key] ?? $default ?? (static::$defaults[$key] ?? null);
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::forgetCache();
    }

    public static function forgetCache(): void
    {
        Cache::forget('shop_settings');
        // Also clear after the surrounding transaction commits, so a cache rebuilt
        // mid-transaction never keeps uncommitted values.
        DB::afterCommit(fn () => Cache::forget('shop_settings'));
    }

    public static function isCounter(string $key): bool
    {
        return str_ends_with($key, '_next_no');
    }
}
