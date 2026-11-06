@props([
    'class' => '',
])

@php
    $attrs = $attributes->merge([
        'class' => 'form-control ' . $class,
    ]);
@endphp

{{ html()->textarea()->attributes($attrs) }}