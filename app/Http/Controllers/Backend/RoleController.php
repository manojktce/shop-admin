<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BaseController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends BaseController
{
    protected static string $permission = 'role';
    protected $module = 'role';
    protected $model = Role::class;

    public function _shareData($page)
    {
        if ($page == 'create' || $page == 'edit') {
            return [
                'permission_list' => Permission::get()->groupBy('group_name')
            ];
        }
    }

    public function _query($model)
    {
        // Admin is granted everything by Gate::before and is mirrored from users.role by name,
        // so it cannot be edited or deleted here.
        return $model->with('permissions')->where('name', '!=', User::$admin);
    }

    public function _beforeSave($request)
    {
        $data = ['name' => $request['name'] ?? null];

        // The built-in Staff role keeps its name (users.role is mirrored to it by name).
        if ($this->record && $this->record->name === User::$staff) {
            $data['name'] = User::$staff;
        }

        return $data;
    }

    public function _beforeDelete($role)
    {
        abort_if(in_array($role->name, [User::$admin, User::$staff], true), 403, 'Built-in roles cannot be deleted.');
    }

    public function _validation($id) 
    {
        return [
            'rules' => [
                'name' => 'required',
            ]
        ];
    }

    public function _afterSave($role)
    {
        $role->syncPermissions(request()->permissions ?? []);

        return true;
    }
}