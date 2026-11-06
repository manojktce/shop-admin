<?php

namespace App\DataTables\Backend;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

class CategoryDataTable extends BaseDataTable
{
    protected $module = 'categories';
    protected $order = [2, 'asc'];
    protected $action = ['edit', 'delete'];

    public function query(Category $model): QueryBuilder
    {
        return $model->newQuery()->select('id', 'name', 'sort_order', 'is_active');
    }

    public function columns($query)
    {
        return $query
            ->editColumn('is_active', fn ($row) => badges('status', $row->is_active))
            ->rawColumns(['is_active', 'action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('name')->title('Category'),
            Column::make('sort_order')->title('Kiosk tile order')->searchable(false),
            Column::make('is_active')->title('Status')->searchable(false),
            Column::computed('action'),
        ];
    }
}
