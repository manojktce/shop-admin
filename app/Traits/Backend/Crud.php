<?php

namespace App\Traits\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\Middleware;
use App\Traits\Backend\Cruds\ActionTrait;
use App\Traits\Backend\Cruds\ModelTrait;
use App\Traits\Backend\Cruds\ResponseTrait;
use App\Traits\Backend\Cruds\ValidationTrait;
use App\Traits\Backend\Cruds\ViewTrait;

trait Crud
{
    use ActionTrait;
    use ModelTrait;
    use ResponseTrait;
    use ValidationTrait;
    use ViewTrait;

    public static function middleware(): array
    {
        $permission = static::$permission ?? null;

        if (!$permission) {
            return [];
        }
        
        return [
            new Middleware("permission:{$permission}.list", only: ['index']),
            new Middleware("permission:{$permission}.create", only: ['create', 'store']),
            new Middleware("permission:{$permission}.show", only: ['show']),
            new Middleware("permission:{$permission}.edit", only: ['edit', 'update']),
            new Middleware("permission:{$permission}.delete", only: ['destroy']),
        ];
    }

    protected function initializeCrud()
    {
        $this->dt = $this->dt ?? str_replace('/', '\\', ucwords($this->module, '/'));
        $this->view = $this->view ?? str_replace('/', '.', $this->module);
        $this->message = $this->message ?? ucfirst(basename($this->module));
        $this->redirect = $this->redirect ?? Str::plural($this->module);
        $this->isDataTable = $this->isDataTable ?? true;
    }

    public function index()
    {
        $this->initializeCrud();

        $viewData = $this->_viewData('list');

        if ($this->isDataTable) {
            $dt = "\\App\DataTables\\Backend\\{$this->dt}DataTable";
            $dataTable = app($dt);

            if (method_exists($dataTable, 'setViewData')) {
                $dataTable->setViewData($viewData);
            }

            return $dataTable->render("backend.{$this->view}.list", $viewData);
        } else {
            return view("backend.{$this->view}", $viewData);
        }
    }

    public function create()
    {
        $this->initializeCrud();

        return view("backend.{$this->view}.create", $this->_viewData('create'));
    }

    public function store(Request $request)
    {
        $this->initializeCrud();

        return $this->_action($request);
    }

    public function show(string $id)
    {
        $this->initializeCrud();
        
        $record = $this->_select($id);

        if ($record instanceof \Illuminate\Http\RedirectResponse) {
            return $record;
        }

        return view("backend.{$this->view}.show", $this->_viewData('show', compact('id', 'record')));
    }

    public function edit(string $id)
    {
        $this->initializeCrud();

        $record = $this->_select($id);

        if ($record instanceof \Illuminate\Http\RedirectResponse) {
            return $record;
        }

        return view("backend.{$this->view}.edit", $this->_viewData('edit', compact('id', 'record')));
    }

    public function update(Request $request, string $id)
    {
        $this->initializeCrud();

        $record = $this->_select($id);

        if ($record instanceof \Illuminate\Http\RedirectResponse) {
            return $record;
        }
        
        return $this->_action($request, $id);
    }

    public function destroy(string $id)
    {
        $this->initializeCrud();
        
        return $this->_destroy($id);
    }
}
