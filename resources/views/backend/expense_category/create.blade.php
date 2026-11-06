<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Expense categories" :crumbs="[['title' => 'All Expense categories', 'link' => baseURL('expense-categories')], ['title' => 'Create']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('expense-categories') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('post', baseURL('expense-categories'))->open() }}
                @include('backend.expense_category.partials.form')
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
