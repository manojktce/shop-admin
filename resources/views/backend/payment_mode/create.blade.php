<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Payment modes" :crumbs="[['title' => 'All Payment modes', 'link' => baseURL('payment-modes')], ['title' => 'Create']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('payment-modes') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('post', baseURL('payment-modes'))->open() }}
                @include('backend.payment_mode.partials.form')
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>
