<?php

namespace App\DataTables\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

class UserDataTable extends BaseDataTable
{
    protected $module = 'users';
    protected $order = [1, 'asc'];
    protected $action = ['edit'];

    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->select('id', 'name', 'username', 'email', 'role', 'is_active');
    }

    public function columns($query)
    {
        return $query
            ->editColumn('role', fn ($row) => User::$roles[$row->role] ?? $row->role)
            ->editColumn('is_active', fn ($row) => badges('status', $row->is_active))
            ->rawColumns(['is_active', 'action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('name'),
            Column::make('username'),
            Column::make('email'),
            Column::make('role'),
            Column::make('is_active')->title('Status')->searchable(false),
            Column::computed('action'),
        ];
    }
}
