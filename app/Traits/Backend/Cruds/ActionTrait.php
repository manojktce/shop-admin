<?php

namespace App\Traits\Backend\Cruds;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ActionTrait
{
    protected function _action(Request $request, $id = null)
    {
        $validate = $this->_validate($id);
        
        if ($validate) {
            return $validate;
        }

        try {
            return DB::transaction(function () use ($request, $id) {
                $requestData = $request->all();

                if (method_exists($this, '_beforeSave')) {
                    $requestData = $this->_beforeSave($requestData);
                }

                $model = app($this->model);

                if ($id != '') {
                    $model = $model->find($id);
                }

                $result = $model->fill($requestData)->save();

                if (method_exists($this, '_afterSave')) {
                    $result = $this->_afterSave($model);
                }

                if (!$result) {
                    return $this->_errorResponse('Something went wrong.');
                }

                $message = $id ? "{$this->message} updated successfully." : "{$this->message} created successfully.";

                return $this->_successResponse($message);
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->_errorResponse('Something went wrong.');
        }
    }

    protected function _destroy($id)
    {
        $record = $this->_select($id);

        if ($record instanceof \Illuminate\Http\RedirectResponse) {
            return $record;
        }

        try {
            return DB::transaction(function () use ($record) {
                if (method_exists($this, '_beforeDelete')) {
                    $this->_beforeDelete($record);
                }

                $result = $record->delete();

                if (method_exists($this, '_afterDelete')) {
                    $result = $this->_afterDelete($record);
                }

                if (!$result) {
                    return $this->_errorResponse('Something went wrong.');
                }

                return $this->_successResponse("{$this->message} deleted successfully.");
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->_errorResponse('Something went wrong.');
        }
    }
}
