@props(['id', 'title', 'confirmLabel'])

<dialog
    id="{{ $id }}"
    class="confirmation-modal"
    aria-labelledby="{{ $id }}-judul"
    aria-describedby="{{ $id }}-pesan"
>
    <h2 id="{{ $id }}-judul" class="text-2xl font-semibold text-terracotta">{{ $title }}</h2>
    <p id="{{ $id }}-pesan" class="mt-2 text-muted">{{ $slot }}</p>
    <form method="dialog" class="mt-6 flex flex-wrap justify-end gap-3">
        <x-button variant="secondary" value="cancel" autofocus>Batal</x-button>
        <x-button value="confirm">{{ $confirmLabel }}</x-button>
    </form>
</dialog>
