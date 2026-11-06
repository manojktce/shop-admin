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
                'email' => 'required|email|unique:users,email'.($id == '' ? '' : ','.$id),
                'password' => $id=='' ? 'required' : '',
                'mobile' => 'required|numeric'
            ]
        ];
    }

    public function _beforeSave($request) 
    {
        $request['image'] = $this->uploadFile(
            $this->_model()->image_storage_path,
            'image',
            $request['image_hidden']
        );
        
        if ($request['password']=='') {
            unset($request['password']);
        }
        
        return $request;
    }
}
