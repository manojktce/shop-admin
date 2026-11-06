<?php

namespace App\DataTables\Backend;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

/** Product list; the search box matches code and name (requirements §4.1). */
class ProductDataTable extends BaseDataTable
{
    protected $module = 'products';
    protected $order = [2, 'asc'];
    protected $action = ['edit', 'delete'];

    public function query(Product $model): QueryBuilder
    {
        return $model->newQuery()
            ->select('id', 'code', 'name', 'category_id', 'base_unit_id', 'tax_id', 'selling_price', 'min_stock', 'current_stock', 'is_active')
            ->with(['category:id,name', 'baseUnit:id,short_name', 'tax:id,name']);
    }

    public function columns($query)
    {
        $qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');

        return $query
            ->addColumn('category', fn ($row) => $row->category?->name)
            ->addColumn('gst', fn ($row) => $row->tax?->name)
            ->editColumn('selling_price', fn ($row) => number_format((float) $row->selling_price, 2) . ' / ' . $row->baseUnit?->short_name)
            ->editColumn('current_stock', fn ($row) => $qty($row->current_stock) . ' ' . $row->baseUnit?->short_name . ($row->isLowStock() ? ' (low)' : ''))
            ->editColumn('min_stock', fn ($row) => $qty($row->min_stock) . ' ' . $row->baseUnit?->short_name)
            ->editColumn('is_active', fn ($row) => badges('status', $row->is_active))
            ->rawColumns(['is_active', 'action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('code'),
            Column::make('name'),
            Column::computed('category')->title('Category'),
            Column::make('selling_price')->title('Selling price')->searchable(false),
            Column::computed('gst')->title('GST'),
            Column::make('current_stock')->title('Stock')->searchable(false),
            Column::make('min_stock')->title('Min stock')->searchable(false),
            Column::make('is_active')->title('Status')->searchable(false),
            Column::computed('action'),
        ];
    }
}
