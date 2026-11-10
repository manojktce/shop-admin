@php
    $selected = (string) ($row['unit_id'] ?? '');
@endphp
<div class="row g-2 mb-2 quick-qty-row align-items-start">
    <div class="col-5 col-md-3">
        <input type="number" name="quick_qtys[{{ $i }}][qty]" class="form-control" min="0" step="any" placeholder="Qty" value="{{ $row['qty'] ?? '' }}">
        @if (is_int($i))
            <x-backend::form.error name="quick_qtys.{{ $i }}.qty" :errors="$errors"/>
        @endif
    </div>
    <div class="col-5 col-md-3">
        <select name="quick_qtys[{{ $i }}][unit_id]" class="form-control quick-unit">
            <option value="">Unit</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" data-base="{{ $unit->baseUnitId() }}" @selected($selected === (string) $unit->id)>{{ $unit->short_name }}</option>
            @endforeach
        </select>
        @if (is_int($i))
            <x-backend::form.error name="quick_qtys.{{ $i }}.unit_id" :errors="$errors"/>
        @endif
    </div>
    <div class="col-2">
        <button type="button" class="btn btn-outline-danger remove-quick-qty" title="Remove"><i class="ti ti-trash"></i></button>
    </div>
</div>
