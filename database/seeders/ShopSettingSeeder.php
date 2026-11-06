<?php

namespace Database\Seeders;

use App\Models\ShopSetting;
use Illuminate\Database\Seeder;

/** Creates any missing setting with its default value; never overwrites saved settings. */
class ShopSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ShopSetting::$defaults as $key => $value) {
            ShopSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        ShopSetting::forgetCache();
    }
}
