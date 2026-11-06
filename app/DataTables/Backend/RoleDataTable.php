<?php

namespace App\DataTables\Backend;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

use App\DataTables\Backend\BaseDataTable;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleDataTable extends BaseDataTable
{
    protected $module = 'roles';
    protected $order = [1, 'asc'];
    protected $action = ['edit', 'delete'];

    public function query(Role $model): QueryBuilder
    {
        return $model->newQuery()->whereNotIn('name', [User::$superAdmin, User::$customer])->with('permissions');
    }

    public function columns($query)
    {
        return $query
                ->editColumn('permissions', function($data) {
                    return $data->permissions
                        ->groupBy('group_name')
                        ->map(function ($permissions, $group) {
                            $actions = $permissions->map(function ($p) {
                                return ucfirst(explode('.', $p->name)[1]);
                            })->implode(', ');

                            return '<b>' . ucfirst(rtrim($group, 's')) . '</b>: ' . $actions;

                        })
                        ->implode('<br>');
                })
                ->rawColumns(['permissions', 'action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('name')->title('Role'),
            Column::make('permissions')->title('Permissions')->orderable(false)->searchable(false),
            Column::computed('action'),
        ];
    }
}
