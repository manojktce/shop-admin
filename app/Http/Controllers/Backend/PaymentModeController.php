<?php

namespace App\Http\Controllers\Backend;

use App\Models\PaymentMode;
use Illuminate\Validation\Rule;

/** Payment modes master (requirements §3.3; XLS → payment_modes). Admin only. */
class PaymentModeController extends BaseController
{
    protected static string $permission = 'payment_mode';
    protected $module = 'payment_mode';
    protected $model = PaymentMode::class;
    protected $dt = 'PaymentMode';
    protected $redirect = 'payment-modes';
    protected $message = 'Payment mode';

    public function _validation($id)
    {
        return [
            'rules' => [
                'name' => ['required', 'string', 'max:50', Rule::unique('payment_modes', 'name')->ignore($id)],
            ],
        ];
    }

    public function _beforeSave($request)
    {
        return [
            'name' => trim($request['name']),
            'affects_cash_drawer' => request()->boolean('affects_cash_drawer'),
            'is_active' => request()->boolean('is_active'),
        ];
    }
}
