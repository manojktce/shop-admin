<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Expense categories" :crumbs="[['title' => 'All Expense categories', 'link' => baseURL('expense-categories')], ['title' => 'Edit']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('expense-categories') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('put', baseURL("expense-categories/{$id}"))->open() }}
                @include('backend.expense_category.partials.form', ['record' => $record])
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
