<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Users" :crumbs="[['title' => 'All Users', 'link' => baseURL('users')], ['title' => 'Edit']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('users') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('put', baseURL("users/{$id}"))->open() }}
                @include('backend.user.partials.form', ['record' => $record])
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
