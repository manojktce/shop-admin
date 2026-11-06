<?php

namespace App\Http\Controllers\Backend;

use App\Models\ExpenseCategory;
use Illuminate\Validation\Rule;

/** Expense categories master (requirements §3.3; XLS → expense_categories). Admin only. */
class ExpenseCategoryController extends BaseController
{
    protected static string $permission = 'expense_category';
    protected $module = 'expense_category';
    protected $model = ExpenseCategory::class;
    protected $dt = 'ExpenseCategory';
    protected $redirect = 'expense-categories';
    protected $message = 'Expense category';

    public function _validation($id)
    {
        return [
            'rules' => [
                'name' => ['required', 'string', 'max:100', Rule::unique('expense_categories', 'name')->ignore($id)],
            ],
        ];
    }

    public function _beforeSave($request)
    {
        return [
            'name' => trim($request['name']),
            'is_active' => request()->boolean('is_active'),
        ];
    }
}
