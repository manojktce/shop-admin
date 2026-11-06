@props([
    'type' => 'text',
    'class' => ''
])

@php
    $attrs = $attributes->merge([
        'class' => 'form-control ' . $class,
    ]);
@endphp

{{ html()->input($type)->attributes($attrs) }}