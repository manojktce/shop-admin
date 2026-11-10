<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Stock" :crumbs="[['title' => $low ? 'Low stock' : 'Stock list']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="card">
        <div class="card-body">
            <form method="get" class="row g-2 mb-3 align-items-center">
                <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search name or short code" value="{{ request('q') }}"></div>
                <div class="col-auto">
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="low" value="1" id="low" @checked($low)><label class="form-check-label" for="low">Low stock only</label></div>
                </div>
                <div class="col-auto"><button class="btn btn-outline-secondary">Filter</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr><th>Code</th><th>Product</th><th class="text-end">Current stock</th><th class="text-end">Minimum</th>@if ($low)<th class="text-end">Short by</th>@endif<th></th></tr></thead>
                    <tbody>
                    @forelse ($products as $product)
                        @php $unit = $product->baseUnit?->short_name; @endphp
                        <tr>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->name }}@unless ($product->is_active) <span class="badge bg-secondary">inactive</span>@endunless</td>
                            <td class="text-end {{ $product->isLowStock() ? 'text-danger fw-bold' : '' }}">{{ rtrim(rtrim(number_format((float) $product->current_stock, 3, ".", ""), "0"), ".") }} {{ $unit }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float) $product->min_stock, 3, ".", ""), "0"), ".") }} {{ $unit }}</td>
                            @if ($low)
                                <td class="text-end">{{ rtrim(rtrim(number_format((float) (float) $product->min_stock - (float) $product->current_stock, 3, ".", ""), "0"), ".") }} {{ $unit }}</td>
                            @endif
                            <td><a href="{{ baseURL('stock/' . $product->id . '/ledger') }}" class="btn btn-sm btn-outline-primary">Ledger</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">{{ $low ? 'No products are below minimum stock.' : 'No products found.' }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $products->links() }}
        </div>
    </div>
</x-backend.layouts.full>
