@php
    $name = $user->name;
    $email = $user->email;
    $image = $user->image_url != '' && fileExists($user->image_url) ? $user->image_url : url('assets/backend/images/profile/user.jpg');
@endphp

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Sumanas Admin Panel</title>

        <link rel="shortcut icon" type="image/png" href="{{ url('assets/backend/images/logos/favicon.png') }}" />
        <link rel="stylesheet" href="{{ url('assets/backend/css/styles.css') }}" />
        @stack('styles')
        <link rel="stylesheet" href="{{ url('assets/backend/css/custom.css') }}" />
    </head>
    <body class="link-sidebar">
        <div class="preloader">
            <img src="{{ url('assets/backend/images/logos/favicon.png') }}" alt="loader" class="lds-ripple img-fluid" />
        </div>
        <div id="main-wrapper">
            @include('backend.components.layouts.partials.sidebar')
            <div class="page-wrapper">
                @include('backend.components.layouts.partials.header')

                <div class="body-wrapper">
                    @isset($breadcrumb)
                        {{ $breadcrumb }}
                    @endisset
                    <div class="container-fluid" id="app">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ url('assets/backend/js/vendor.min.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/simplebar/js/simplebar.min.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/theme/js/app.init.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/theme/js/theme.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/theme/js/app.min.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/theme/js/sidebarmenu.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
        @stack('scripts')

        <script>
            $('.logout').click(function() {
                var form = $('{{ html()->form("post", "")->open() }}{{ html()->form()->close() }}').appendTo('body');
                form.attr('action', $(this).data('url')).submit();
            })
        </script>

        @vite('resources/js/backend/app.js')
    </body>
</html>