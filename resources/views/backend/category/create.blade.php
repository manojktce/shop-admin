<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Categories" :crumbs="[['title' => 'All Categories', 'link' => baseURL('categories')], ['title' => 'Create']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('categories') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('post', baseURL('categories'))->open() }}
                @include('backend.category.partials.form')
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
