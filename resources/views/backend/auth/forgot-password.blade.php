<x-backend.layouts.blank>
    <p class="mb-7">Please enter your email to reset your password.</p>
    <x-backend::ui.alerts />
    {{ html()->form('post', baseURL('forgot-password'))->open() }}
        <div class="mb-3">
            <x-backend::form.label name="Email" for="email" required/>
            <x-backend::form.input name="email" id="email" placeholder="Enter Email" value="{{ old('email') }}"/>
            <x-backend::form.error name="email" :errors="$errors"/>
        </div>
        <div class="text-end mb-4">
            <a class="text-primary fw-medium" href="{{ baseURL() }}">Back to Login</a>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Submit</button>
    {{ html()->form()->close() }}
</x-backend.layouts.blank>