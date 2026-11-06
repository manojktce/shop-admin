<?php

namespace App\DataTables\Backend;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;

class UnitDataTable extends BaseDataTable
{
    protected $module = 'units';
    protected $order = [1, 'asc'];
    protected $action = ['edit', 'delete'];

    public function query(Unit $model): QueryBuilder
    {
        return $model->newQuery()->select('id', 'name', 'short_name', 'base_unit_id', 'conversion_factor')->with('baseUnit:id,short_name');
    }

    public function columns($query)
    {
        return $query
            ->addColumn('base_unit', fn ($row) => $row->baseUnit?->short_name ?? $row->short_name)
            ->editColumn('conversion_factor', fn ($row) => rtrim(rtrim($row->conversion_factor, '0'), '.'))
            ->addColumn('meaning', fn ($row) => $row->isBase()
                ? 'Base unit'
                : '1 ' . $row->short_name . ' = ' . rtrim(rtrim($row->conversion_factor, '0'), '.') . ' ' . $row->baseUnit?->short_name)
            ->rawColumns(['action']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('name'),
            Column::make('short_name')->title('Short name'),
            Column::computed('base_unit')->title('Base unit'),
            Column::make('conversion_factor')->title('Conversion factor')->searchable(false),
            Column::computed('meaning')->title('Meaning'),
            Column::computed('action'),
        ];
    }
}
