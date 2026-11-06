<header class="topbar">
    <div class="with-vertical">
        <nav class="navbar navbar-expand-lg p-0">
            <ul class="navbar-nav">
                <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <i class="ti ti-menu-2 text-white"></i>
                    </a>
                </li>
            </ul>
            <div class="d-block d-lg-none py-4">
                <a href="javascript:void(0)" class="text-nowrap logo-img">
                    <img src="{{ url('assets/backend/images/logos/logo.png') }}" alt="logo" width="145"/>
                </a>
            </div>
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                <li class="nav-item dropdown">
                    <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <div class="user-profile-img">
                                <img src="{{ $image }}" class="rounded-circle" width="35" height="35" alt="profile" />
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                        <div class="profile-dropdown position-relative" data-simplebar>
                            <div class="py-3 px-7 pb-0">
                                <h5 class="mb-0 fs-5 fw-semibold">User Profile</h5>
                            </div>
                            <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                                <img src="{{ $image }}" class="rounded-circle" width="80" height="80" alt="profile" />
                                <div class="ms-3">
                                    <h5 class="mb-1 fs-3">{{ $name }}</h5>
                                    <p class="mb-0 d-flex align-items-center gap-2">
                                        <i class="ti ti-mail fs-4"></i> {{ $email }}
                                    </p>
                                </div>
                            </div>
                            <div class="message-body">
                                <a href="{{ baseURL('profile') }}" class="py-8 px-7 mt-8 d-flex align-items-center">
                                    <span class="d-flex align-items-center justify-content-center text-bg-light rounded-1 p-6">
                                        <img src="{{ url('assets/backend/images/svgs/icon-account.svg') }}" alt="modernize-img" width="24" height="24" />
                                    </span>
                                    <div class="w-100 ps-3">
                                        <h6 class="mb-1 fs-3 fw-semibold lh-base">My Profile</h6>
                                        <span class="fs-2 d-block text-body-secondary">Account Settings</span>
                                    </div>
                                </a>
                            </div>
                            <div class="d-grid py-4 px-7 pt-8">
                                <a href="javascript:void(0);" class="btn btn-outline-primary logout" data-url="{{ baseURL('logout') }}">Log Out</a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</header>