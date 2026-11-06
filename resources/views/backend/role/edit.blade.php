<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb 
            title="Roles"
            :crumbs="[
                ['title' => 'All Roles', 'link' => baseURL('roles')],
                ['title' => 'Edit']
            ]"
        />
    </x-slot:breadcrumb>
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('put', baseURL("roles/{$id}"))->open() }}
                @include('backend.role.partials.form', ['record' => $record])
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>