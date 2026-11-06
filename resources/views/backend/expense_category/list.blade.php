<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Expense categories" :crumbs="[['title' => 'All Expense categories']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />

    @permission($permissions['create'])
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ baseURL('expense-categories/create') }}" class="btn btn-dark">Create</a>
        </div>
    @endpermission

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <x-backend::datatable.table :dataTable="$dataTable"/>
            </div>
        </div>
    </div>
</x-backend.layouts.full>
