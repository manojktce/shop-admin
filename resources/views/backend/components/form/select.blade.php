<div class="col-12">
    @props([
        'class' => '',
        'value' => '',
        'options' => [],
        'optionsKey' => '',
        'optionsValue' => '',
        'placeholder' => null,
    ])

    @php
        if ($optionsKey && $optionsValue && is_object($options)) {
            $options = $options->pluck($optionsValue, $optionsKey)->toArray();
        }

        if (filled($placeholder)) {
            $options = ['' => $placeholder] + $options;
        }
        
        $attrs = $attributes->merge([
            'class' => 'form-control ' . $class,
        ]);
    @endphp

    {{ 
        html()
        ->select()
        ->options($options)
        ->value($value)
        ->attributes($attrs) 
    }}
</div>

@push('styles')
    @once
        <link rel="stylesheet" href="{{ url('assets/backend/plugins/select2/css/select2.min.css') }}" />
    @endonce
@endpush

@push('scripts')
    @once
        <script src="{{ url('assets/backend/plugins/select2/js/select2.min.js') }}"></script>
        <script>
            function select2(el) {
                if (el.data('select2')) return;
                el.select2();
            }
        </script>
    @endonce

    <script>
        $(function() {
            let selector;

            if ('{{ $attributes->has('id') }}' == true) {
                selector = "#{{ $attributes->get('id') }}";
            } else {
                selector = 'select[name="{{ $attributes->get('name') }}"]';
            }

            let el = $(selector);

            select2(el);
        })
    </script>
@endpush
