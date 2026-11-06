<x-backend.layouts.blank>
    <p class="mb-7">Please sign in to access the dashboard.</p>
    <x-backend::ui.alerts />
    {{ html()->form('post', baseURL())->open() }}
        <div class="mb-3">
            <x-backend::form.label name="Username" for="username" required/>
            <x-backend::form.input name="username" id="username" placeholder="Enter Username" value="{{ old('username') }}" autocomplete="username"/>
            <x-backend::form.error name="username" :errors="$errors"/>
        </div>
        <div class="mb-4">
            <x-backend::form.label name="Password" for="password" required/>
            <x-backend::form.input type="password" name="password" id="password" placeholder="Enter Password" value=""/>
            <x-backend::form.error name="password" :errors="$errors"/>
        </div>
        <div class="text-end mb-4">
            <a class="text-primary fw-medium" href="{{ baseURL('forgot-password') }}">Forgot Password ?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Sign In</button>
    {{ html()->form()->close() }}
</x-backend.layouts.blank>