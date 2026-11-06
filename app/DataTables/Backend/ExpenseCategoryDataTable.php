<?php

namespace App\DataTables\Backend;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

class ExpenseCategoryDataTable extends BaseDataTable
{
    protected $module = 'expense-categories';
    protected $order = [1, 'asc'];
    protected $action = ['edit', 'delete'];

    public function query(ExpenseCategory $model): QueryBuilder
    {
        return $model->newQuery()->select('id', 'name', 'is_active');
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
            Column::make('name')->title('Expense category'),
            Column::make('is_active')->title('Status')->searchable(false),
            Column::computed('action'),
        ];
    }
}
