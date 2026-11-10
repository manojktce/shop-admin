<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Stock in" :crumbs="[['title' => 'Stock entries']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('stock-entries/create') }}" class="btn btn-dark">New stock in</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="get" class="row g-2 mb-3">
                <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search entry no." value="{{ request('q') }}"></div>
                <div class="col-auto"><button class="btn btn-outline-secondary">Search</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr><th>Entry no.</th><th>Date</th><th>Lines</th><th class="text-end">Amount</th><th>Entered by</th><th>Notes</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->entry_no }}</td>
                            <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td>{{ $entry->items_count }}</td>
                            <td class="text-end">{{ number_format((float) $entry->items_sum_amount, 2) }}</td>
                            <td>{{ $entry->creator?->name }}</td>
                            <td>{{ $entry->notes }}</td>
                            <td><a href="{{ baseURL('stock-entries/' . $entry->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No stock entries yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $entries->links() }}
        </div>
    </div>
</x-backend.layouts.full>
