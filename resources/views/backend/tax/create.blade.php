<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Tax (GST)" :crumbs="[['title' => 'All Tax (GST)', 'link' => baseURL('taxes')], ['title' => 'Create']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('taxes') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('post', baseURL('taxes'))->open() }}
                @include('backend.tax.partials.form')
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
