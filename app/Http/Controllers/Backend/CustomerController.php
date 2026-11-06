<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BaseController;
use App\Models\User;

class CustomerController extends BaseController
{
    protected static string $permission = 'customer';
    protected $module = 'customer';
    protected $model = User::class;

    public function _query($query) 
    {
        return $query->customers();
    }

    public function _shareData($page)
    {
        if ($page == 'create' || $page == 'edit'){
            return ['genders' => ['' => 'Select Gender']+User::$gender];
        }
    }
    
    public function _validation($id) 
    {
        return [
            'rules' => [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => $id=='' ? 'required' : 'nullable',
                'mobile' => 'required|numeric'
            ]
        ];
    }

    public function _beforeSave($request) 
    {
        if ($request['password'] == '') {
            unset($request['password']);
        }

        $request['status'] = request()->boolean('status');

        return $request;
    }

    public function _afterSave($result) 
    {
        if (!$result->hasRole(User::$customer)) {
            $result->assignRole(User::$customer);
        }

        return true;
    }
}
