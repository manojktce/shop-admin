@if ($errors->has($name))
    <p style="margin:2px 0 0;color: red;font-size: 12px;">
        {{ $errors->first($name) }}
    </p>
@endif