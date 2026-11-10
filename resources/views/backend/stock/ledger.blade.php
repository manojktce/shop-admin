<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Stock ledger" :crumbs="[['title' => 'Stock list', 'link' => baseURL('stock')], ['title' => $product->name]]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    @php $unit = $product->baseUnit?->short_name; @endphp
    <div class="card">
        <div class="card-body">
            <h5>{{ $product->code }} - {{ $product->name }}</h5>
            <p>Current stock: <strong>{{ rtrim(rtrim(number_format((float) $product->current_stock, 3, ".", ""), "0"), ".") }} {{ $unit }}</strong></p>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th class="text-end">Qty in</th><th class="text-end">Qty out</th><th class="text-end">Balance</th></tr></thead>
                    <tbody>
                    @forelse ($movements as $m)
                        @php
                            $qty = (float) $m->base_qty;
                            $item = $m->reference_type === 'stock_entry_items' ? $items->get($m->reference_id) : null;
                            $adj = $m->reference_type === 'stock_adjustments' ? $adjustments->get($m->reference_id) : null;
                        @endphp
                        <tr>
                            <td>{{ $m->movement_date->format('d/m/Y H:i') }}</td>
                            <td>{{ \App\Models\StockMovement::TYPES[$m->type] ?? $m->type }}</td>
                            <td>
                                @if ($item?->entry)
                                    <a href="{{ baseURL('stock-entries/' . $item->entry->id) }}">{{ $item->entry->entry_no }}</a>
                                @elseif ($adj)
                                    Adjustment - {{ $adj->reasonLabel() }}
                                @else
                                    {{ $m->reference_type }} #{{ $m->reference_id }}
                                @endif
                            </td>
                            <td class="text-end">{{ $qty > 0 ? rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.') : '' }}</td>
                            <td class="text-end">{{ $qty < 0 ? rtrim(rtrim(number_format(abs($qty), 3, '.', ''), '0'), '.') : '' }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float) $m->balance_after, 3, ".", ""), "0"), ".") }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No stock movements yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $movements->links() }}
        </div>
    </div>
</x-backend.layouts.full>
