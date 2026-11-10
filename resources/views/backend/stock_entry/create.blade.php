@php
    $rows = old('items', [['product_id' => '', 'unit_id' => '', 'qty' => '', 'cost_per_base_unit' => '']]);
@endphp
<x-backend.layouts.full>
    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb title="Stock in" :crumbs="[['title' => 'Stock entries', 'link' => baseURL('stock-entries')], ['title' => 'New']]" />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ baseURL('stock-entries') }}" class="btn btn-dark">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->form('post', baseURL('stock-entries'))->open() }}
                <div class="row g-3">
                    <div class="col-md-3">
                        <x-backend::form.label name="Date" for="entry_date" required/>
                        <x-backend::form.input type="date" name="entry_date" id="entry_date" max="{{ now()->toDateString() }}" value="{{ old('entry_date', now()->toDateString()) }}"/>
                        <x-backend::form.error name="entry_date" :errors="$errors"/>
                    </div>
                    <div class="col-md-9">
                        <x-backend::form.label name="Notes" for="notes"/>
                        <x-backend::form.input name="notes" id="notes" maxlength="255" placeholder="Supplier, bill no., etc." value="{{ old('notes') }}"/>
                        <x-backend::form.error name="notes" :errors="$errors"/>
                    </div>
                    <div class="col-12">
                        <x-backend::form.label name="Products" for="stock-items" required/>
                        <p class="tagline">Quantity can be in any unit of the product (e.g. 1 qtl adds 100 kg). Cost is per base unit and becomes the product's latest cost.</p>
                        <div id="stock-items">
                            @foreach ($rows as $i => $row)
                                @include('backend.stock_entry.partials.item-row', ['i' => $i, 'row' => $row])
                            @endforeach
                        </div>
                        <x-backend::form.error name="items" :errors="$errors"/>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-item">Add product</button>
                        <template id="item-template">
                            @include('backend.stock_entry.partials.item-row', ['i' => '__INDEX__', 'row' => []])
                        </template>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            {{ html()->form()->close() }}
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const list = $('#stock-items');
                const template = $('#item-template').html();
                const max = {{ \App\Http\Controllers\Backend\StockEntryController::MAX_ITEMS }};
                let next = list.children().length;

                // Only offer units that convert to the chosen product's base unit; show the base unit beside the cost.
                function filterRow(row) {
                    const opt = row.find('select.item-product option:selected');
                    const base = String(opt.data('base') || '');
                    row.find('select.item-unit option[data-base]').each(function () {
                        const ok = base !== '' && String($(this).data('base')) === base;
                        $(this).prop('hidden', !ok).prop('disabled', !ok);
                    });
                    const unit = row.find('select.item-unit');
                    if (unit.find('option:selected').prop('disabled')) unit.val('');
                    row.find('.base-label').text(opt.data('unit') ? 'per ' + opt.data('unit') : '');
                }

                $('#add-item').on('click', function () {
                    if (list.children().length >= max) return;
                    list.append(template.replaceAll('__INDEX__', next++));
                });
                list.on('click', '.remove-item', function () {
                    if (list.children().length > 1) $(this).closest('.stock-item-row').remove();
                });
                list.on('change', 'select.item-product', function () { filterRow($(this).closest('.stock-item-row')); });
                list.children().each(function () { filterRow($(this)); });
            });
        </script>
    @endpush
</x-backend.layouts.full>
