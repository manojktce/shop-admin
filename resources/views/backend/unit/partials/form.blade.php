@php
    $record = $record ?? null;
    $factor = old('conversion_factor', $record && !$record->isBase() ? rtrim(rtrim($record->conversion_factor, '0'), '.') : '');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-backend::form.label name="Name" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="e.g. Kilogram" value="{{ old('name', $record?->name) }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Short name" for="short_name" required/>
        <x-backend::form.input name="short_name" id="short_name" placeholder="Shown on bill, e.g. kg" value="{{ old('short_name', $record?->short_name) }}"/>
        <x-backend::form.error name="short_name" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Base unit" for="base_unit_id"/>
        <x-backend::form.select name="base_unit_id" id="base_unit_id" placeholder="None – this is a base unit" value="{{ old('base_unit_id', $record?->base_unit_id) }}" :options="$baseUnits"/>
        <x-backend::form.error name="base_unit_id" :errors="$errors"/>
        <p class="tagline">Stock is always stored in the base unit.</p>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Conversion factor" for="conversion_factor"/>
        <x-backend::form.input type="number" name="conversion_factor" id="conversion_factor" min="0" step="any" placeholder="1 unit = factor × base unit" value="{{ $factor }}"/>
        <x-backend::form.error name="conversion_factor" :errors="$errors"/>
        <p class="tagline">Example: 1 g = 0.001 kg, 1 quintal = 100 kg. A base unit is always 1.</p>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>
