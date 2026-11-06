<?php

namespace App\Traits\Backend\Cruds;

use Illuminate\Support\Facades\Validator;

trait ValidationTrait
{
    protected function _validate($id = null)
    {
        if (!method_exists($this, '_validation')) {
            return null;
        }

        $validation = $this->_validation($id);

        return $this->_validator($validation['rules'] ?? [], $validation['messages'] ?? []);
    }

    protected function _validator($rules=[], $messages=[])
    {
        $validator = Validator::make(request()->all(), $rules, $messages);
 
        if ($validator->fails()) {
            if (request()->ajax()) {
                return apiResponse(false, "Bad request", $validator->errors(), 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        return null;
    }
}
