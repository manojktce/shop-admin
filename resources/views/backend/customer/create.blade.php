<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb 
            title="Customers"
            :crumbs="[
                ['title' => 'All Customers', 'link' => baseURL('customers')],
                ['title' => 'Create']
            ]"
        />
    </x-slot:breadcrumb>
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('post', baseURL('customers'))->open() }}
                @include('backend.customer.partials.form')
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>