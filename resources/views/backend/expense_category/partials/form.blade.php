@php
    $record = $record ?? null;
    $isActive = old('is_active', $record ? (int) $record->is_active : 1);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-backend::form.label name="Expense category" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="e.g. Tea & snacks" value="{{ old('name', $record?->name) }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Active" for="is_active"/>
        <x-backend::form.switch name="is_active" value="1" checked="{{ $isActive }}"/>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>
