<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Stock in" :crumbs="[['title' => 'Stock entries', 'link' => baseURL('stock-entries')], ['title' => $entry->entry_no]]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('stock-entries') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Entry no.</strong><br>{{ $entry->entry_no }}</div>
                <div class="col-md-3"><strong>Date</strong><br>{{ $entry->entry_date->format('d/m/Y') }}</div>
                <div class="col-md-3"><strong>Entered by</strong><br>{{ $entry->creator?->name }}</div>
                <div class="col-md-3"><strong>Notes</strong><br>{{ $entry->notes ?: '-' }}</div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr><th>Product</th><th class="text-end">Qty entered</th><th class="text-end">Added to stock</th><th class="text-end">Cost / base unit</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                    @foreach ($entry->items as $item)
                        <tr>
                            <td>{{ $item->product?->code }} - {{ $item->product?->name }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->qty, 3, ".", ""), "0"), ".") }} {{ $item->unit?->short_name }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->base_qty, 3, ".", ""), "0"), ".") }} {{ $item->product?->baseUnit?->short_name }}</td>
                            <td class="text-end">{{ number_format((float) $item->cost_per_base_unit, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="text-end">{{ number_format((float) $entry->items->sum('amount'), 2) }}</th></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</x-backend.layouts.full>
