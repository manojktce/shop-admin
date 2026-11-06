<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb 
            title="Customers"
            :crumbs="[
                ['title' => 'All Customers']
            ]"
        />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />

    @permission($permissions['create'])
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ baseURL('customers/create') }}" class="btn btn-dark">Create</a>
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

