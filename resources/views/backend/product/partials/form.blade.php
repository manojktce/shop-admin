@php
    $record = $record ?? null;
    $isActive = old('is_active', $record ? (int) $record->is_active : 1);
    $loose = old('allow_decimal_qty', $record ? (int) $record->allow_decimal_qty : 0);
    $num = fn ($v) => $v === null || $v === '' ? '' : rtrim(rtrim((string) $v, '0'), '.');
    $quickQtys = old('quick_qtys', $record
        ? $record->quickQtys->map(fn ($q) => ['unit_id' => $q->unit_id, 'qty' => $num($q->qty)])->all()
        : []);
@endphp

<div class="row g-3">
    <div class="col-md-3">
        <x-backend::form.label name="Code" for="code" required/>
        <x-backend::form.input name="code" id="code" placeholder="e.g. R01" value="{{ old('code', $record?->code) }}"/>
        <x-backend::form.error name="code" :errors="$errors"/>
    </div>
    <div class="col-md-5">
        <x-backend::form.label name="Name" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="Shown on bill" value="{{ old('name', $record?->name) }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-4">
        <x-backend::form.label name="Category" for="category_id" required/>
        <x-backend::form.select name="category_id" id="category_id" placeholder="Select" value="{{ old('category_id', $record?->category_id) }}" :options="$categories"/>
        <x-backend::form.error name="category_id" :errors="$errors"/>
    </div>
    <div class="col-md-4">
        <x-backend::form.label name="Base unit" for="base_unit_id" required/>
        <x-backend::form.select name="base_unit_id" id="base_unit_id" placeholder="Select" value="{{ old('base_unit_id', $record?->base_unit_id) }}" :options="$baseUnits" :disabled="$stockLocked"/>
        @if ($stockLocked)
            <input type="hidden" name="base_unit_id" value="{{ $record->base_unit_id }}">
            <p class="tagline">Cannot change while the product has stock.</p>
        @else
            <p class="tagline">Stock is counted and priced in this unit.</p>
        @endif
        <x-backend::form.error name="base_unit_id" :errors="$errors"/>
    </div>
    <div class="col-md-4">
        <x-backend::form.label name="GST" for="tax_id" required/>
        <x-backend::form.select name="tax_id" id="tax_id" placeholder="Select" value="{{ old('tax_id', $record?->tax_id) }}" :options="$taxes"/>
        <x-backend::form.error name="tax_id" :errors="$errors"/>
    </div>
    <div class="col-md-4">
        <x-backend::form.label name="Sell loose" for="allow_decimal_qty"/>
        <x-backend::form.switch name="allow_decimal_qty" value="1" checked="{{ $loose }}"/>
        <p class="tagline">Allow decimal quantity (50 g, 1.5 kg).</p>
    </div>
    <div class="col-md-4">
        <x-backend::form.label name="Selling price (per base unit, incl. GST)" for="selling_price" required/>
        <x-backend::form.input type="number" name="selling_price" id="selling_price" min="0" step="0.01" value="{{ old('selling_price', $record?->selling_price) }}"/>
        <x-backend::form.error name="selling_price" :errors="$errors"/>
    </div>
    <div class="col-md-4">
        <x-backend::form.label name="Cost price (per base unit)" for="cost_price"/>
        <x-backend::form.input type="number" name="cost_price" id="cost_price" min="0" step="0.01" value="{{ old('cost_price', $record?->cost_price) }}"/>
        <x-backend::form.error name="cost_price" :errors="$errors"/>
    </div>
    <div class="col-md-4">
        <x-backend::form.label name="Minimum stock (base unit)" for="min_stock"/>
        <x-backend::form.input type="number" name="min_stock" id="min_stock" min="0" step="0.001" value="{{ old('min_stock', $num($record?->min_stock)) }}"/>
        <x-backend::form.error name="min_stock" :errors="$errors"/>
        <p class="tagline">Alert when stock falls below this.</p>
    </div>
    @if ($record)
        <div class="col-md-4">
            <x-backend::form.label name="Current stock" for="current_stock"/>
            <input type="text" id="current_stock" class="form-control" value="{{ $num($record->current_stock) }} {{ $record->baseUnit?->short_name }}" disabled>
            <p class="tagline">Changed only through stock in, adjustments and sales.</p>
        </div>
    @endif

    <div class="col-12">
        <x-backend::form.label name="Quick quantities" for="quick-qtys"/>
        <p class="tagline">One-tap buttons for billing and kiosk, e.g. 250 g, 500 g, 1 kg. Up to {{ \App\Http\Controllers\Backend\ProductController::MAX_QUICK_QTYS }}.</p>
        <div id="quick-qtys">
            @foreach ($quickQtys as $i => $row)
                @include('backend.product.partials.quick-qty-row', ['i' => $i, 'row' => $row])
            @endforeach
        </div>
        <x-backend::form.error name="quick_qtys" :errors="$errors"/>
        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-quick-qty">Add quick quantity</button>
        <template id="quick-qty-template">
            @include('backend.product.partials.quick-qty-row', ['i' => '__INDEX__', 'row' => []])
        </template>
    </div>

    <div class="col-md-6">
        <x-backend::form.label name="Image (kiosk tile)" for="image"/>
        <input type="file" name="image" id="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        <x-backend::form.error name="image" :errors="$errors"/>
        @if ($record?->image_path)
            <img src="{{ $record->image_url }}" alt="{{ $record->name }}" class="mt-2" style="max-height:80px">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                <label class="form-check-label" for="remove_image">Remove image</label>
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Active" for="is_active"/>
        <x-backend::form.switch name="is_active" value="1" checked="{{ $isActive }}"/>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            const list = $('#quick-qtys');
            const template = $('#quick-qty-template').html();
            const max = {{ \App\Http\Controllers\Backend\ProductController::MAX_QUICK_QTYS }};
            let next = list.children().length;

            // Only offer units that convert to the chosen base unit.
            function filterUnits() {
                const base = String($('#base_unit_id').val() || '');
                list.find('select.quick-unit option[data-base]').each(function () {
                    const ok = base !== '' && String($(this).data('base')) === base;
                    $(this).prop('hidden', !ok).prop('disabled', !ok);
                });
            }

            $('#add-quick-qty').on('click', function () {
                if (list.children().length >= max) return;
                list.append(template.replaceAll('__INDEX__', next++));
                filterUnits();
            });

            list.on('click', '.remove-quick-qty', function () {
                $(this).closest('.quick-qty-row').remove();
            });

            $('#base_unit_id').on('change', filterUnits);
            filterUnits();
        });
    </script>
@endpush
