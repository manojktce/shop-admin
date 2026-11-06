@php
$sidebarMenu = [
    [
        'title' => 'Dashboard',
        'icon' => 'dashboard',
        'url'  => baseURL('dashboard'),
        'permission' => null,
    ],
    [
        'title' => 'Customers',
        'icon' => 'user',
        'url'  => baseURL('customers'),
        'permission' => 'customer.list',
    ],
    [
        'title' => 'Settings',
        'icon' => 'settings',
        'children' => [
            [
                'title' => 'Roles',
                'icon' => 'circle',
                'url'  => baseURL('roles'),
                'permission' => 'role.list',
            ]
        ]
    ]
];
@endphp

<aside class="left-sidebar with-vertical">
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-center">
            <a href="javascript:void(0)" class="text-nowrap logo-img">
                <img src="{{ url('assets/backend/images/logos/logo.png') }}" alt="logo" width="140"/>
            </a>
            <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
                <i class="ti ti-x"></i>
            </a>
        </div>

        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <ul id="sidebarnav">
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Menu</span>
                </li>

                @foreach($sidebarMenu as $item)
                    @php
                        $hasChildren = isset($item['children']);
                        if ($hasChildren) {
                            $visibleChildren = collect($item['children'])
                                ->filter(fn($child) => !isset($child['permission']) || auth()->user()->can($child['permission']))
                                ->toArray();
                        } else {
                            $canShow = !isset($item['permission']) || auth()->user()->can($item['permission'] ?? '');
                        }
                    @endphp

                    @if(($hasChildren && count($visibleChildren)) || (!$hasChildren && $canShow))
                        @if($hasChildren)
                            <li class="sidebar-item">
                                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                    <span class="d-flex"><i class="ti ti-{{ $item['icon'] }}"></i></span>
                                    <span class="hide-menu">{{ $item['title'] }}</span>
                                </a>
                                <ul aria-expanded="false" class="collapse first-level">
                                    @foreach($visibleChildren as $child)
                                        <li class="sidebar-item">
                                            <a href="{{ $child['url'] }}" class="sidebar-link">
                                                <div class="round-16 d-flex align-items-center justify-content-center">
                                                    <i class="ti ti-{{ $child['icon'] }}"></i>
                                                </div>
                                                <span class="hide-menu">{{ $child['title'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ $item['url'] }}">
                                    <span><i class="ti ti-{{ $item['icon'] }}"></i></span>
                                    <span class="hide-menu">{{ $item['title'] }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
