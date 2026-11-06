@props([
    'title' => '',
    'crumbs' => [],
])

<div class="card shadow-none position-relative overflow-hidden mb-3 rounded-0">
    <div class="card-body px-4 py-3">
        <div class="row">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <h4 class="fw-semibold m-0">{{ $title }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="{{ baseURL() }}">Home</a>
                        </li>
                        @foreach ($crumbs as $crumb)
                            @if (isset($crumb['link']))
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ $crumb['link'] }}">
                                        {{ $crumb['title'] }}
                                    </a>
                                </li>
                            @else 
                                <li class="breadcrumb-item" aria-current="page">{{ $crumb['title'] }}</li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>