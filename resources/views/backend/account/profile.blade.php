<x-backend.layouts.full>
    @php
        $name = $record['name'] ?? old('name');
        $email = $record['email'] ?? old('email');
        $mobile = $record['mobile'] ?? old('mobile');
        $dob =  $record['dob'] ? dateFormat(3, $record['dob']) : old('dob');
        $gender = $record['gender'] ?? old('gender');
        $image = $record['image'];
    @endphp

    <x-slot:breadcrumb>
        <x-backend::ui.breadcrumb 
            title="Profile"
            :crumbs="[
                ['title' => 'Profile']
            ]"
        />
    </x-slot:breadcrumb>
    <x-backend::ui.alerts />
    <div class="card">
        <div class="card-body">
            {{ html()->form('put', baseURL('profile'))->acceptsFiles()->open() }}
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
                        <x-backend::form.label name="Password" for="password"/>
                        <x-backend::form.input type="password" name="password" id="password" placeholder="Enter Password"/>
                        <x-backend::form.error name="password" :errors="$errors"/>
                    </div>
                    <div class="col-md-6">
                        <x-backend::form.label name="Mobile Number" for="mobile" required/>
                        <x-backend::form.input name="mobile" id="mobile" placeholder="Enter Mobile Number" value="{{ $mobile }}"/>
                        <x-backend::form.error name="mobile" :errors="$errors"/>
                    </div>
                    <div class="col-md-6">
                        <x-backend::form.label name="Date of Birth" for="dob"/>
                        <x-backend::form.flatpickr name="dob" id="dob" placeholder="Enter Date of Birth" value="{{ $dob }}"/>
                    </div>
                    <div class="col-md-6">
                        <x-backend::form.label name="Gender" for="gender"/>
                        <x-backend::form.select 
                            name="gender" 
                            id="gender" 
                            value="{{ $gender }}"
                            :options="$genders"
                        />
                        <x-backend::form.error name="gender" :errors="$errors"/>
                    </div>
                    <div class="col-md-12">
                        <x-backend::form.label name="Image" for="image"/>
                        <x-backend::form.file name="image" class="image" file="{{ $image }}" path="{{ $image_path }}"/>
                        <p class="tagline">(Image Size - 490 x 490)</p>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            {{ html()->form()->close() }}
        </div>
    </div>
</x-backend.layouts.full>