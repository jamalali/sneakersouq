@props(['active'])

@php
$classes = ($active ?? false)
            ? 'navbar-item is-tab is-active'
            : 'navbar-item is-tab';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
