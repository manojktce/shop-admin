<?php

namespace App\Traits\Backend\Cruds;

trait ViewTrait
{
    protected function _permission()
    {
        $permission = static::$permission ?? null;

        if (!$permission) {
            return [];
        }
        
        return [
            'permissions' => [
                'list'   => "{$permission}.list",
                'create' => "{$permission}.create",
                'show'   => "{$permission}.show",
                'edit'   => "{$permission}.edit",
                'delete' => "{$permission}.delete",
            ],
        ];
    }

    protected function _viewData($page, $extra = [])
    {
        $shareData = [];

        if (method_exists($this, '_shareData')) {
            $shareData = $this->_shareData($page, $extra['id'] ?? '') ?? [];
        }

        return array_merge($shareData, $this->_permission(), $extra);
    }
}
