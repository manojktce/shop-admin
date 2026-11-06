<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Products" :crumbs="[['title' => 'All Products', 'link' => baseURL('products')], ['title' => 'Edit']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('products') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('put', baseURL("products/{$id}"))->acceptsFiles()->open() }}
                @include('backend.product.partials.form', ['record' => $record])
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
