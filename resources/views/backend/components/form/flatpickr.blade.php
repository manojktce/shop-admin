@props([
    'mode' => 'date',
    'class' => '',
])

<div class="input-group">
    <x-backend::form.input {{ $attributes->merge(['class' => $class]) }} />
    <span class="input-group-text">
        <i class="ti ti-calendar fs-5"></i>
    </span>
</div>

@push('styles')
    @once
        <link rel="stylesheet" href="{{ url('assets/backend/plugins/flatpickr/css/flatpickr.min.css') }}" />
    @endonce
@endpush

@push('scripts')
    @once
        <script src="{{ url('assets/backend/plugins/flatpickr/js/flatpickr.js') }}"></script>
        <script>
            function datepicker(el) {
                el.flatpickr({
                    enableTime: false,
                    dateFormat: "d/m/Y",
                });
            }

            function daterangepicker(el) {
                el.flatpickr({
                    mode: "range",
                    enableTime: false,
                    dateFormat: "d/m/Y",
                });
            }

            function timepicker(el) {
                el.flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    minuteIncrement: 1
                });
            }
        </script>
    @endonce

    <script>
        $(function() {
            let selector;
            let mode = '{{ $mode }}';

            if ('{{ $attributes->has('id') }}' == true) {
                selector = "#{{ $attributes->get('id') }}";
            } else {
                selector = 'input[name="{{ $attributes->get('name') }}"]';
            }

            if (mode == 'date') {
                datepicker($(selector));
            } else if (mode == 'daterange') {
                daterangepicker($(selector));
            } else if (mode == 'time') {
                timepicker($(selector));
            }
        })
    </script>
@endpush