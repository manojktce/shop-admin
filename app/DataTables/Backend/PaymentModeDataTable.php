<?php

namespace App\DataTables\Backend;

use App\Models\PaymentMode;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

class PaymentModeDataTable extends BaseDataTable
{
    protected $module = 'payment-modes';
    protected $order = [1, 'asc'];
    protected $action = ['edit', 'delete'];

    public function query(PaymentMode $model): QueryBuilder
    {
        return $model->newQuery()->select('id', 'name', 'affects_cash_drawer', 'is_active');
    }

    public function columns($query)
    {
        return $query
            ->editColumn('affects_cash_drawer', fn ($row) => $row->affects_cash_drawer ? 'Yes' : 'No')
            ->editColumn('is_active', fn ($row) => badges('status', $row->is_active))
            ->rawColumns(['is_active', 'action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('name')->title('Mode'),
            Column::make('affects_cash_drawer')->title('Goes to drawer cash')->searchable(false),
            Column::make('is_active')->title('Status')->searchable(false),
            Column::computed('action'),
        ];
    }
}
