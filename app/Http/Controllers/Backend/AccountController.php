<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BaseController;
use Illuminate\Http\Request;
use App\Traits\Backend\Media;
use App\Models\User;

class AccountController extends BaseController
{
    use Media;

    protected $module = 'profile';
    protected $redirect = 'profile';
    protected $model = User::class;

    public function profile(Request $request)
    {
        $this->initializeCrud();
        $id = auth()->user()->id;

        $record = $this->_select($id);
        $image_path = $this->_model()->image_storage_path;

        if ($record instanceof \Illuminate\Http\RedirectResponse) {
            return $record;
        }
        
        if ($request->isMethod('put')) {
            return $this->_action($request, $id);
        }

        $genders = ['' => 'Select Gender']+User::$gender;

        return view('backend.account.profile', compact('record', 'genders', 'image_path'));
    }

    public function _validation($id) 
    {
        return [
            'rules' => [
                'name' => 'required',
                'email' => 'nullable|email|max:150|unique:users,email'.($id == '' ? '' : ','.$id),
                'password' => 'nullable|string|min:6',
                'mobile' => 'nullable|numeric'
            ]
        ];
    }

    public function _beforeSave($request) 
    {
        // Users may change only their own profile fields — never role, username or active flag.
        $request = array_intersect_key($request, array_flip(['name', 'email', 'password', 'mobile', 'dob', 'gender', 'image_hidden']));
        $request['image_hidden'] = $request['image_hidden'] ?? null;

        $request['image'] = $this->uploadFile(
            $this->_model()->image_storage_path,
            'image',
            $request['image_hidden']
        );
        
        if (blank($request['password'] ?? null)) {
            unset($request['password']);
        }
        
        return $request;
    }
}
