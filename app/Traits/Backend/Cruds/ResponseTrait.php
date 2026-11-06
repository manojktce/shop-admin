<?php

namespace App\Traits\Backend\Cruds;

trait ResponseTrait
{
    protected function _successResponse($message, $data = [], $redirect = null) 
    {
        if (request()->ajax()) {
            return apiResponse(true, $message, $data);
        }

        return redirect($redirect ?? baseURL($this->redirect ?? ''))->with('success', $message);
    }

    protected function _errorResponse($message = 'Something went wrong.', $data = [], $redirect = null) {
        if (request()->ajax()) {
            return apiResponse(false, $message, $data, 500);
        }

        return ($redirect ? redirect($redirect) : redirect()->back()->withInput())->with('danger', $message);
    }
}
