@php
    $record = $record ?? [];
    $name = $record['name'] ?? old('name');
    $permissions = isset($record['permissions']) ? $record['permissions']->pluck('name')->toArray() : (old('permissions') ?? []);
@endphp

<div class="row g-3">
    <div class="col-md-12">
        <x-backend::form.label name="Role Name" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="Enter Role Name" value="{{ $name }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-12">
        <x-backend::form.label name="Permissions" for="permissions"/>
        <div class="row">
            @foreach ($permission_list as $group => $permission)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-3">
                        <h5 class="card-title">{{ ucfirst(rtrim($group, 's')) }}</h5>
                        @foreach ($permission as $p)
                            <div class="d-flex align-items-center">
                                <x-backend::form.switch name="permissions[]" value="{{ $p->name }}" id="permission{{ $p->id }}" style="width: 40px; height: 25px;" checked="{{ in_array($p->name, $permissions) }}"/>
                                <label class="m-0 fs-4 ms-2" for="permission{{ $p->id }}">{{ ucfirst(explode('.', $p->name)[1]) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>