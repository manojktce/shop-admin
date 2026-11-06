<?php

namespace App\Traits\Backend\Cruds;

trait ModelTrait
{
    protected function _model()
    {
        return $this->record instanceof $this->model ? $this->record : app($this->model);
    }

    protected function _select($id)
    {
        $model = app($this->model);

        if (method_exists($this, '_query')) {
            $record = $this->_query($model->where('id', $id))->first();
        } else { 
            $record = $model->find($id);
        }

        if (!$record) {
            abort(404, "{$this->message} not found.");
        }

        $this->record = $record;
        return $record;
    }
}
