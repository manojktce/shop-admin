<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Products" :crumbs="[['title' => 'All Products']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />

    @permission($permissions['create'])
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ baseURL('products/create') }}" class="btn btn-dark">Create</a>
        </div>
    @endpermission

    <div class="card">
        <div class="card-body">
            <p class="tagline mb-2">Search by product name or short code.</p>
            <div class="table-responsive">
                <x-backend::datatable.table :dataTable="$dataTable"/>
            </div>
        </div>
    </div>
</x-backend.layouts.full>
