@php
    $record = $record ?? null;
    $isActive = old('is_active', $record ? (int) $record->is_active : 1);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-backend::form.label name="Category" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="e.g. Rice & grains" value="{{ old('name', $record?->name) }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-3">
        <x-backend::form.label name="Kiosk tile order" for="sort_order" required/>
        <x-backend::form.input type="number" name="sort_order" id="sort_order" min="0" step="1" value="{{ old('sort_order', $record?->sort_order ?? 0) }}"/>
        <x-backend::form.error name="sort_order" :errors="$errors"/>
    </div>
    <div class="col-md-3">
        <x-backend::form.label name="Active" for="is_active"/>
        <x-backend::form.switch name="is_active" value="1" checked="{{ $isActive }}"/>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>
