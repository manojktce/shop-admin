@props([
    'class' => '',
    'checked' => null,
])

@php
    $attrs = $attributes->merge([
        'class' => 'form-check-input ' . $class,
        'style' => 'width:50px;height:30px;',
    ]);

    if ($checked == 1) {
        $attrs = $attrs->merge(['checked' => 'checked']);
    }
@endphp

<div class="form-check form-switch m-1 d-flex">
    {{ html()->checkbox()->attributes($attrs) }}
</div>