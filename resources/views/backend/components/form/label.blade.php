@props([
    'name' => null, 
    'for' => null,
    'class' => null,
    'required' => null
])

@php
    $attrs = $attributes->merge([
        'class' => 'form-label' . ($required ? ' required' : '') . ($class ? ' '. $class : ''),
    ]);
@endphp

{{ html()->label($name, $for)->attributes($attrs) }}

@push('styles')
    @once
        <style>
            label.required::after {
                content: " *";
                color: red; 
                font-weight: bold;
            }
        </style>
    @endonce
@endpush