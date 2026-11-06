<?php

namespace App\DataTables\Backend;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class BaseDataTable extends DataTable
{
    protected $viewData = [];

    public function setViewData($data)
    {
        $this->viewData = $data;

        return $this;
    }

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $queries = (new EloquentDataTable($query))->addIndexColumn();

        if (count($this->action)) {
            $queries = $this->actionColumn($queries);
        }
        
        if (method_exists($this, 'columns')) {
            $queries = $this->columns($queries);
        }

        return $queries;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                ->setTableId(str_replace(['/'], ['-'], $this->module) . "-table")
                ->dom("<'row' <'col-6' l><'col-6' f>><'row' <'col-12' tr>><'row' <'col-6' i><'col-6' p>>")
                ->autoWidth(false)
                ->columns($this->getColumns())
                ->orderBy($this->order)
                ->minifiedAjax();
    }

    protected function filename(): string
    {
        return ucfirst(str_replace(['/'], ['-'], $this->module)) . '_' . date('YmdHis');
    }

    protected function actionColumn($data)
    {
        $user = auth()->user();
        $permissions = $this->viewData['permissions'];

        return $data->addColumn('action', function($row) use($user, $permissions) {
            $action = $this->action;
            $result = '';

            if (in_array('edit', $action) && $user->can($permissions['edit'])) {
                $result .= '<a href="'. baseURL("{$this->module}/{$row->id}/edit") .'" class="btn btn-secondary"><i class="fs-5 ti ti-pencil"></i></a>';
            }

            if (in_array('view', $action) && $user->can($permissions['show'] ?? null)) {
                $result .= '<a href="'. baseURL("{$this->module}/{$row->id}") .'" class="btn btn-success"><i class="fs-5 ti ti-eye"></i></a>';
            }

            if (in_array('delete', $action) && $user->can($permissions['delete'])) {
                $result .= '<a href="javascript:void(0)" class="btn btn-danger destroy" data-url="'. baseURL("{$this->module}/{$row->id}") .'"><i class="fs-5 ti ti-trash"></i></a>';
            }
            
            return "<div class='datatable-icons'>{$result}</div>";
        });
    }
}
