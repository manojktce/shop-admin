<?php

namespace App\DataTables\Backend;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

class TaxDataTable extends BaseDataTable
{
    protected $module = 'taxes';
    protected $order = [2, 'asc'];
    protected $action = ['edit', 'delete'];

    public function query(Tax $model): QueryBuilder
    {
        return $model->newQuery()->select('id', 'name', 'rate', 'is_active');
    }

    public function columns($query)
    {
        return $query
            ->editColumn('rate', fn ($row) => rtrim(rtrim($row->rate, '0'), '.') . '%')
            ->editColumn('is_active', fn ($row) => badges('status', $row->is_active))
            ->rawColumns(['is_active', 'action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('name')->title('Tax name'),
            Column::make('rate')->searchable(false),
            Column::make('is_active')->title('Status')->searchable(false),
            Column::computed('action'),
        ];
    }
}
