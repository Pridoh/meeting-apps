@props(['href', 'light' => false])

<a href="{{ $href }}" class="brand-lockup">
    <img src="{{ asset($light ? 'rapatin-logo-light.png' : 'rapatin-logo.png') }}" width="185" height="80" alt="RAPATIN">
</a>
