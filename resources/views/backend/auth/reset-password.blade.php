<x-backend.layouts.blank>
    <p class="mb-7">Please enter your new password.</p>
    <x-backend::ui.alerts />
    {{ html()->form('post', baseURL('reset-password'))->open() }}
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="mb-3">
            <x-backend::form.label name="Password" for="password" required/>
            <x-backend::form.input type="password" name="password" id="password" placeholder="Enter Password" value=""/>
            <x-backend::form.error name="password" :errors="$errors"/>
        </div>
        <div class="mb-3">
            <x-backend::form.label name="Confirm Password" for="password_confirmation" required/>
            <x-backend::form.input type="password" name="password_confirmation" id="password_confirmation" placeholder="Enter Confirm Password" value=""/>
            <x-backend::form.error name="password_confirmation" :errors="$errors"/>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Reset Password</button>
    {{ html()->form()->close() }}
</x-backend.layouts.blank>