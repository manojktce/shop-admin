@php
    $record = $record ?? null;
    $isActive = old('is_active', $record ? (int) $record->is_active : 1);
    $affectsDrawer = old('affects_cash_drawer', $record ? (int) $record->affects_cash_drawer : 0);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-backend::form.label name="Mode" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="e.g. UPI" value="{{ old('name', $record?->name) }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-3">
        <x-backend::form.label name="Goes to drawer cash" for="affects_cash_drawer"/>
        <x-backend::form.switch name="affects_cash_drawer" value="1" checked="{{ $affectsDrawer }}"/>
        <p class="tagline">On = counts in daily cash.</p>
    </div>
    <div class="col-md-3">
        <x-backend::form.label name="Active" for="is_active"/>
        <x-backend::form.switch name="is_active" value="1" checked="{{ $isActive }}"/>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>
