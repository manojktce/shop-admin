<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BaseController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
        return $model->with('permissions');
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