<?php

namespace App\Http\Controllers\Backend;

use App\Models\Tax;

/** Tax (GST) master (requirements §3.3; XLS → taxes). Admin only. */
class TaxController extends BaseController
{
    protected static string $permission = 'tax';
    protected $module = 'tax';
    protected $model = Tax::class;

    public function _validation($id)
    {
        return [
            'rules' => [
                'name' => 'required|string|max:50',
                'rate' => 'required|numeric|min:0|max:100|decimal:0,2',
            ],
        ];
    }

    public function _beforeSave($request)
    {
        return [
            'name' => trim($request['name']),
            'rate' => $request['rate'],
            'is_active' => request()->boolean('is_active'),
        ];
    }
}
