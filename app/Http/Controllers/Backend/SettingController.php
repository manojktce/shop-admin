<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ShopSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

/** Shop settings screen (requirements §3.2, §6.4 negative stock). Admin only. */
class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:setting.edit')];
    }

    public function edit()
    {
        $settings = collect(ShopSetting::$defaults)->mapWithKeys(fn ($v, $k) => [$k => ShopSetting::get($k)]);

        return view('backend.setting.edit', [
            'settings' => $settings,
            'printSizes' => ShopSetting::$printSizes,
            'roundings' => ShopSetting::$roundings,
        ]);
    }

    public function update(Request $request)
    {
        $request->merge(['gstin' => strtoupper(trim((string) $request->input('gstin')))]);

        $data = $request->validate([
            'shop_name' => 'required|string|max:150',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gstin' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z0-9]{13}$/'],
            'invoice_prefix' => 'required|string|max:20',
            'invoice_next_no' => 'required|integer|min:1|max:99999999',
            'receipt_prefix' => 'required|string|max:20',
            'receipt_next_no' => 'required|integer|min:1|max:99999999',
            'stock_entry_prefix' => 'required|string|max:20',
            'stock_entry_next_no' => 'required|integer|min:1|max:99999999',
            'print_size' => ['required', Rule::in(array_keys(ShopSetting::$printSizes))],
            'bill_rounding' => ['required', Rule::in(array_keys(ShopSetting::$roundings))],
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'gstin.regex' => 'GSTIN must be 15 characters, e.g. 33ABCDE1234F1Z5.',
        ]);

        $data['gstin'] = $data['gstin'] ?? '';
        $data['allow_negative_stock'] = $request->boolean('allow_negative_stock') ? '1' : '0';

        $oldLogo = ShopSetting::get('logo');
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('shop', 'public');
        }

        DB::transaction(function () use (&$data) {
            // Counters: lock them and never move one backwards, so a form opened before
            // newer bills were saved cannot cause duplicate numbers.
            $errors = [];

            foreach (array_keys($data) as $key) {
                if (! ShopSetting::isCounter($key)) {
                    continue;
                }

                $current = (int) ShopSetting::where('key', $key)->lockForUpdate()->value('value');

                if ((int) $data[$key] === $current) {
                    unset($data[$key]);
                } elseif ((int) $data[$key] < $current) {
                    $errors[$key] = "The next number cannot be lower than {$current} (numbers already used).";
                }
            }

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            foreach ($data as $key => $value) {
                ShopSetting::put($key, (string) $value);
            }
        });

        if (isset($data['logo']) && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return redirect(baseURL('settings'))->with('success', 'Settings updated successfully.');
    }
}
