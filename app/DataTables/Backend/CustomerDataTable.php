<?php

namespace App\DataTables\Backend;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

use App\DataTables\Backend\BaseDataTable;
use App\Models\User;

class CustomerDataTable extends BaseDataTable
{
    protected $module = 'customers';
    protected $order = [5, 'desc'];
    protected $action = ['edit', 'delete'];

    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->customers();
    }

    public function columns($query)
    {
        return $query
                ->editColumn('created_at', function($data) {
                    return dateFormat(3, $data->created_at);
                })
                ->editColumn('status', function($data) {
                    return badges('status', $data->status);
                })
                ->rawColumns(['status', 'action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('name'),
            Column::make('email'),
            Column::make('mobile'),
            Column::make('status'),
            Column::make('created_at'),
            Column::computed('action'),
        ];
    }
}
