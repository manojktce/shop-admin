@php
    $record = $record ?? null;
    $isActive = old('is_active', $record ? (int) $record->is_active : 1);
    $isSelf = $record && $record->id === auth()->id();
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-backend::form.label name="Name" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="Full name" value="{{ old('name', $record?->name) }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Username" for="username" required/>
        <x-backend::form.input name="username" id="username" placeholder="Login name" value="{{ old('username', $record?->username) }}"/>
        <x-backend::form.error name="username" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Email" for="email"/>
        <x-backend::form.input name="email" id="email" placeholder="Optional" value="{{ old('email', $record?->email) }}"/>
        <x-backend::form.error name="email" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Role" for="role" required/>
        <x-backend::form.select name="role" id="role" value="{{ old('role', $record?->role ?? 'staff') }}" :options="$roles"/>
        <x-backend::form.error name="role" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="{{ $record ? 'Reset password' : 'Password' }}" for="password" :required="!$record"/>
        <x-backend::form.input type="password" name="password" id="password" placeholder="Password" value="" autocomplete="new-password"/>
        <x-backend::form.error name="password" :errors="$errors"/>
        @if ($record)
            <p class="tagline">Leave blank to keep the current password.</p>
        @endif
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Active" for="is_active"/>
        <x-backend::form.switch name="is_active" value="1" checked="{{ $isActive }}"/>
        @if ($isSelf)
            <p class="tagline">You cannot disable or demote your own account.</p>
        @endif
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>
