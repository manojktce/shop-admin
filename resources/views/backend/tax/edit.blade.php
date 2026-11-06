<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Tax (GST)" :crumbs="[['title' => 'All Tax (GST)', 'link' => baseURL('taxes')], ['title' => 'Edit']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('taxes') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('put', baseURL("taxes/{$id}"))->open() }}
                @include('backend.tax.partials.form', ['record' => $record])
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
