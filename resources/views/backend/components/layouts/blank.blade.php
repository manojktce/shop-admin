<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Sumanas Admin Panel</title>

        <link rel="shortcut icon" type="image/png" href="{{ url('assets/backend/images/logos/favicon.png') }}" />
        <link rel="stylesheet" href="{{ url('assets/backend/css/styles.css') }}" />
    </head>
    <body>
        <div class="preloader">
            <img src="{{ url('assets/backend/images/logos/favicon.png') }}" alt="loader" class="lds-ripple img-fluid" />
        </div>
        <div id="main-wrapper" class="auth-customizer-none">
            <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100">
                <div class="position-relative z-index-5">
                    <div class="row">
                        <div class="col-xl-7 col-xxl-8">
                            <a href="javascript:void(0)" class="text-nowrap logo-img d-block px-4 py-9 w-100">
                                <!-- <img src="{{ url('assets/backend/images/logos/logo.png') }}" alt="logo" /> -->
                            </a>
                            <div class="d-none d-xl-flex align-items-center justify-content-center h-n80">
                                <img src="{{ url('assets/backend/images/backgrounds/login-security.svg') }}" alt="" class="img-fluid" width="500">
                            </div>
                        </div>
                        <div class="col-xl-5 col-xxl-4">
                            <div class="authentication-login min-vh-100 bg-body row justify-content-center align-items-center p-4">
                                <div class="auth-max-width col-sm-8 col-md-6 col-xl-7 px-4">
                                    <h2 class="mb-1 fs-7 fw-bolder">Welcome to Sumanas</h2>
                                    {{ $slot }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ url('assets/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/simplebar/js/simplebar.min.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/theme/js/app.init.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/theme/js/theme.js') }}"></script>
        <script src="{{ url('assets/backend/plugins/theme/js/app.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    </body>
</html>