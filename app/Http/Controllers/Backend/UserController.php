<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Login users (requirements §3.1): Admin adds users, disables them and resets passwords.
 * Users are never deleted, so bills/receipts/expenses keep who entered them.
 */
class UserController extends BaseController
{
    protected static string $permission = 'user';
    protected $module = 'user';
    protected $model = User::class;

    public function _shareData($page)
    {
        return ['roles' => User::$roles];
    }

    public function _validation($id)
    {
        return [
            'rules' => [
                'name' => 'required|string|max:100',
                'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($id)],
                'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id)],
                'password' => $id == '' ? 'required|string|min:6' : 'nullable|string|min:6',
                'role' => ['required', Rule::in(array_keys(User::$roles))],
            ],
        ];
    }

    public function _beforeSave($request)
    {
        $data = array_intersect_key($request, array_flip(['name', 'username', 'email', 'password', 'role']));

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['is_active'] = request()->boolean('is_active');

        // An admin cannot disable or demote their own account.
        if ($this->record && $this->record->id === auth()->id()) {
            $data['is_active'] = true;
            $data['role'] = User::ROLE_ADMIN;
        }

        return $data;
    }
}
