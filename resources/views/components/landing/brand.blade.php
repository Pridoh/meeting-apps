@props(['href' => '#atas', 'label' => 'Rapatin, kembali ke atas'])

<a class="brand" href="{{ $href }}" aria-label="{{ $label }}">
    <span class="brand-mark">
        <img src="{{ asset('image/logo_rapatin.svg') }}" width="1200" height="800" alt="">
    </span>
    <span>Rapatin.</span>
</a>
