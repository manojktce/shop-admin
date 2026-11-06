@php
    $record = $record ?? [];
    $name = $record['name'] ?? old('name');
    $email = $record['email'] ?? old('email');
    $mobile = $record['mobile'] ?? old('mobile');
    $dob = isset($record['dob']) ? dateFormat(3, $record['dob']) : old('dob');
    $gender = $record['gender'] ?? old('gender');
    $status = $record['status'] ?? old('status');
@endphp

<div class="row g-3">
    <div class="col-md-12">
        <x-backend::form.label name="Name" for="name" required/>
        <x-backend::form.input name="name" id="name" placeholder="Enter Name" value="{{ $name }}"/>
        <x-backend::form.error name="name" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Email" for="email" required/>
        <x-backend::form.input name="email" id="email" placeholder="Enter Email" value="{{ $email }}"/>
        <x-backend::form.error name="email" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Password" for="password" required/>
        <x-backend::form.input type="password" name="password" id="password" placeholder="Enter Password" value=""/>
        <x-backend::form.error name="password" :errors="$errors"/>
        @if (isset($id))
            <p class="tagline">Leave blank to keep the current password unchanged.</p>
        @endif
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Mobile Number" for="mobile" required/>
        <x-backend::form.input name="mobile" id="mobile" placeholder="Enter Mobile Number" value="{{ $mobile }}"/>
        <x-backend::form.error name="mobile" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Date of Birth" for="dob" required/>
        <x-backend::form.flatpickr name="dob" id="dob" placeholder="Enter Date of Birth" value="{{ $dob }}"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Gender" for="gender" required/>
        <x-backend::form.select 
            name="gender" 
            id="gender" 
            value="{{ $gender }}"
            :options="$genders"
        />
        <x-backend::form.error name="gender" :errors="$errors"/>
    </div>
    <div class="col-md-6">
        <x-backend::form.label name="Status" for="status"/>
        <x-backend::form.switch name="status" checked="{{ $status }}"/>
        <x-backend::form.error name="status" :errors="$errors"/>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>