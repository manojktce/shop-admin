<div class="row g-2 mb-2 stock-item-row align-items-start">
    <div class="col-12 col-md-4">
        <select name="items[{{ $i }}][product_id]" class="form-control item-product">
            <option value="">Product</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" data-base="{{ $product->base_unit_id }}" data-unit="{{ $product->baseUnit?->short_name }}" @selected((string) ($row['product_id'] ?? '') === (string) $product->id)>{{ $product->code }} - {{ $product->name }}</option>
            @endforeach
        </select>
        @if (is_int($i))
            <x-backend::form.error name="items.{{ $i }}.product_id" :errors="$errors"/>
        @endif
    </div>
    <div class="col-5 col-md-2">
        <input type="number" name="items[{{ $i }}][qty]" class="form-control" min="0" step="any" placeholder="Qty" value="{{ $row['qty'] ?? '' }}">
        @if (is_int($i))
            <x-backend::form.error name="items.{{ $i }}.qty" :errors="$errors"/>
        @endif
    </div>
    <div class="col-5 col-md-2">
        <select name="items[{{ $i }}][unit_id]" class="form-control item-unit">
            <option value="">Unit</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" data-base="{{ $unit->baseUnitId() }}" @selected((string) ($row['unit_id'] ?? '') === (string) $unit->id)>{{ $unit->short_name }}</option>
            @endforeach
        </select>
        @if (is_int($i))
            <x-backend::form.error name="items.{{ $i }}.unit_id" :errors="$errors"/>
        @endif
    </div>
    <div class="col-10 col-md-3">
        <div class="input-group">
            <input type="number" name="items[{{ $i }}][cost_per_base_unit]" class="form-control" min="0" step="0.01" placeholder="Cost" value="{{ $row['cost_per_base_unit'] ?? '' }}">
            <span class="input-group-text base-label"></span>
        </div>
        @if (is_int($i))
            <x-backend::form.error name="items.{{ $i }}.cost_per_base_unit" :errors="$errors"/>
        @endif
    </div>
    <div class="col-2 col-md-1">
        <button type="button" class="btn btn-outline-danger remove-item" title="Remove"><i class="ti ti-trash"></i></button>
    </div>
</div>
