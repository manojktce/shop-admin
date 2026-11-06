<?php

namespace App\Http\Controllers\Backend;

use App\Models\Category;
use Illuminate\Validation\Rule;

/** Categories master (requirements §3.3; XLS → categories). Admin only. */
class CategoryController extends BaseController
{
    protected static string $permission = 'category';
    protected $module = 'category';
    protected $model = Category::class;

    public function _validation($id)
    {
        return [
            'rules' => [
                'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($id)],
                'sort_order' => 'required|integer|min:0|max:100000',
            ],
        ];
    }

    public function _beforeSave($request)
    {
        return [
            'name' => trim($request['name']),
            'sort_order' => (int) $request['sort_order'],
            'is_active' => request()->boolean('is_active'),
        ];
    }
}
