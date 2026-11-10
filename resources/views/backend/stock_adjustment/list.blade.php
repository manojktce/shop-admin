<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Adjustments" :crumbs="[['title' => 'Stock adjustments']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('stock-adjustments/create') }}" class="btn btn-dark">New adjustment</a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr><th>Date</th><th>Product</th><th class="text-end">Change</th><th>Reason</th><th>Notes</th><th>By</th></tr></thead>
                    <tbody>
                    @forelse ($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->adjustment_date->format('d/m/Y') }}</td>
                            <td>{{ $adj->product?->code }} - {{ $adj->product?->name }}</td>
                            <td class="text-end {{ (float) $adj->base_qty < 0 ? 'text-danger' : 'text-success' }}">{{ (float) $adj->base_qty > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $adj->base_qty, 3, ".", ""), "0"), ".") }} {{ $adj->product?->baseUnit?->short_name }}</td>
                            <td>{{ $adj->reasonLabel() }}</td>
                            <td>{{ $adj->notes }}</td>
                            <td>{{ $adj->creator?->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No adjustments yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $adjustments->links() }}
        </div>
    </div>
</x-backend.layouts.full>
